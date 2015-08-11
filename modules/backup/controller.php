<?php
/**
 * Backup Module for Supsystic Backup
 * @package SupsysticBackup\Modules\Backup
 * @version 2.0
 */
class backupControllerEbbs extends controllerEbbs {
    private $_tablesPerStack = 0;

    public function indexAction() {
		$model   = frameEbbs::_()->getModule('backup')->getController()->getModel();
        $backups = dispatcherEbbs::applyFilters('adminGetUploadedFiles', array());
        if(!empty($backups))
            krsort($backups);

        $logs = frameEbbs::_()->getModule('log')->getModel()->getFilesList();

		return $this->render('index', array(
			'backups'   => $backups,
			'logs'     => $logs,
            'model'     => $model
		));
	}

    public function createBackupAction(){
        $request = reqEbbs::get('post');

        if(!empty($request['auth']) && $request['auth'] === AUTH_KEY)
            $this->getModel('backup')->createBackup($request);
    }

	/**
	 * Create Action
	 * Create new backup
	 */
	public function createAction() {
        $request = reqEbbs::get('post');
        $response = new responseEbbs();
        /** @var backupLogTxtModelEbbs $logTxt */
        $logTxt = $this->getModel('backupLogTxt');
        /** @var backupTechLogModelEbbs $techLog */
        $techLog = $this->getModel('backupTechLog');
        /** @var warehouseEbbs $bupFolder */
        $bupFolder = frameEbbs::_()->getModule('warehouse');
        $uploadingList = array();
        $backupComplete = false;

        if(!empty($request['opt_values'])){
            do_action('bupBeforeSaveBackupSettings', $request['opt_values']);
            /* @var $optionsModel optionsModelEbbs*/
            $optionsModel = frameEbbs::_()->getModule('options')->getModel();
            $optionsModel->saveMainFromDestGroup($request);
            $optionsModel->saveGroup($request);
            $optionsModel->refreshOptions();

            // if warehouse changed - create necessary dir
            if (!$bupFolder->getFolder()->exists())
                $bupFolder->getFolder()->create();
        }

        $destination = $this->getModel()->getConfig('dest');
        if($destination !== 'ftp') {
            $isAuthorized = $this->getModel()->checkCloudServiceRemoteServerIsAuth($destination);
            if(!$isAuthorized){
                $response->addError($this->getModel()->getErrors());
                return $response->ajaxExec();
            }
        }

        // We are need to check "warehouse" directory (usually: wp-content/upsupsystic)
        if (!$this->getModel()->checkWarehouse()) {
            $response->addError($this->getModel()->getWarehouseError());
            return $response->ajaxExec();
        }

        if($this->getModel()->isFilesystemRequired() && !$this->checkExtensions($response)) {
            return $response->ajaxExec();
        }

        $currentBackupPath = $this->getModel()->generateFilename(array('zip', 'sql', 'txt'));
        $logTxt->setLogName(basename($currentBackupPath['folder']));
        $logTxt->writeBackupSettings($request['opt_values']);
        $logTxt->add(__('Clear temporary directory', EBBS_LANG_CODE));
        $techLog->deleteOldLogs();
        $techLog->setLogName(basename($currentBackupPath['folder']));

        if ($this->getModel()->isDatabaseRequired()) {
            $logTxt->add(__(sprintf('Start database backup: %s', $currentBackupPath['sql']), EBBS_LANG_CODE));
            $this->getModel()->getDatabase()->create($currentBackupPath['sql']);
            $dbErrors = $this->getModel()->getDatabase()->getErrors();

            if (!empty($dbErrors)) {
                $logTxt->add(__(sprintf('Errors during creation of database backup, errors count %d', count($dbErrors)), EBBS_LANG_CODE));
                $response->addError($dbErrors);
                return $response->ajaxExec();
            }

            $logTxt->add(__('Database backup complete.'), EBBS_LANG_CODE);
            $uploadingList[] = $currentBackupPath['sql'];
            $backupComplete = true;
        }

        if ($this->getModel()->isFilesystemRequired()) {
            if(!file_exists($currentBackupPath['folder'])) {
                $bupFolder->getController()->getModel('warehouse')->create($currentBackupPath['folder'] . DS);
            }

            $logTxt->add(__('Scanning files.', EBBS_LANG_CODE));
            $files = $this->getModel()->getFilesList();
            // $files = array_map('realpath', $files);

            $logTxt->add(sprintf('%s files scanned.', count($files, true) - count($files)));
            $logTxt->add(__('Total stacks: ' . count($files), EBBS_LANG_CODE));
            $techLog->set('stacks', $files);
            $uploadingList[] = $currentBackupPath['folder'];
            $backupComplete = false;
        }

        // if need create filesystem backup or send DB backup on cloud - backup not complete
        if(!empty($files) || $destination !== 'ftp') {
            $backupComplete = false;
            $techLog->set('destination', $destination);
            $techLog->set('uploadingList', $uploadingList);
            $techLog->set('emailNotifications', (frameEbbs::_()->getModule('options')->get('email_ch') == 1) ? true : false);

            $data = array(
                'page' => 'backup',
                'action' => 'createBackupAction',
                'backupId' => $currentBackupPath['folder'],
            );

            if(!empty($files))
                $logTxt->add(__('Send request to generate backup file stacks', EBBS_LANG_CODE));

            $this->getModel('backup')->sendSelfRequest($data);
        }

        if($backupComplete && frameEbbs::_()->getModule('options')->get('email_ch') == 1) {
            $email = frameEbbs::_()->getModule('options')->get('email');
            $subject = __('DropBox Backup by Supsystic Notifications', EBBS_LANG_CODE);

            $logTxt->add(__('Email notification required.', EBBS_LANG_CODE));
            $logTxt->add(sprintf(__('Sending to', EBBS_LANG_CODE) . '%s', $email));

            $message = $logTxt->getContent(false);

            wp_mail($email, $subject, $message);
        }

        $response->addData(array(
            'backupLog' => $logTxt->getContent(),
            'backupId' => basename($currentBackupPath['folder']),
            'backupComplete' => $backupComplete
        ));

        return $response->ajaxExec();

        $cloud = $log->getCurrentBackupFilesName();

        $handlers = $this->getModel()->getDestinationHandlers();

        if (array_key_exists($destination, $handlers)) {

            $cloud = array_map('basename', $cloud);

            $log->string(__(sprintf('Upload to the "%s" required', ucfirst($destination)), EBBS_LANG_CODE));
            $log->string(sprintf('Files to upload: %s', rtrim(implode(', ', $cloud), ', ')));
            $handler = $handlers[$destination];
            $result  = call_user_func_array($handler, array($cloud));
            if ($result === true || $result == 200 || $result == 201) {
                $log->string(__(sprintf('Successfully uploaded to the "%s"', ucfirst($destination)), EBBS_LANG_CODE));

                $path = frameEbbs::_()->getModule('warehouse')->getPath();
                $path = untrailingslashit($path);

                foreach ($cloud as $file) {
                    $log->string(__(sprintf('Removing %s from the local storage.', $file), EBBS_LANG_CODE));
                    if (@unlink($path . '/' . $file)) {
                        $log->string(__(sprintf('%s successfully removed.', $file), EBBS_LANG_CODE));
                    } else {
                        $log->string(__(sprintf('Failed to remove %s', $file), EBBS_LANG_CODE));
                    }
                }
            } else {
                switch ($result) {
                    case 401:
                        $error = __('Authentication required.', EBBS_LANG_CODE);
                        break;
                    case 404:
                        $error = __('File not found', EBBS_LANG_CODE);
                        break;
                    case 500:
                        $error = is_object($handler[0]) ? $handler[0]->getErrors() : __('Unexpected error (500)', EBBS_LANG_CODE);
                        break;
                    default:
                        $error = __('Unexpected error', EBBS_LANG_CODE);
                }

                $log->string(__(
                    sprintf(
                        'Cannot upload to the "%s": %s',
                        ucfirst($destination),
                        is_array($error) ? array_pop($error) : $error
                    )
                , EBBS_LANG_CODE));
            }
        }

        if(empty($error)) {
            $response->addMessage(__('Backup complete.', EBBS_LANG_CODE));
        } else {
            $response->addError(__('Error occurred: ' . ucfirst($destination) . ', ' . $error, EBBS_LANG_CODE));
        }

        // Allow to do new backups.
        $this->unlock();

        $backupPath =  untrailingslashit($bupStorageRoot) . DS;
        $pathInfo = pathinfo($cloud[0]);
        $log->save($backupPath . $pathInfo['filename'] . '.txt');

        $response->addData(
            array(
                'backupLog' => frameEbbs::_()->getModule('backup')->getModel('backupLog')->getBackupLog(),
            )
        );

        $log->clear();

        return $response->ajaxExec();
	}

    public function uploadToCloud() {
        $request = reqEbbs::get('post');
        $response = new responseEbbs();
        /** @var backupLogModelEbbs $log */
        $log = $this->getModel('backupLog');
        $destination = $this->getModel()->getConfig('dest');
        $handlers = $this->getModel()->getDestinationHandlers();

        $file = !empty($request['filename']) ? $request['filename'] : false;

        if(file_exists($file)) {
            $stacksFolder = '';
            $pathInfo = pathinfo($file);
            if($pathInfo['extension'] == 'zip') {
                $stacksFolder = basename($pathInfo['dirname']) . '/';
            }

            $handler = $handlers[$destination];
            $result  = call_user_func_array($handler, array(array($file), $stacksFolder));

            if ($result === true || $result == 200 || $result == 201) {
                $log->string(__(sprintf('Successfully uploaded to the "%s": %s', ucfirst($destination), $file), EBBS_LANG_CODE));

            } else {
                switch ($result) {
                    case 401:
                        $error = __('Authentication required.', EBBS_LANG_CODE);
                        break;
                    case 404:
                        $error = __('File not found', EBBS_LANG_CODE);
                        break;
                    case 500:
                        $error = is_object($handler[0]) ? $handler[0]->getErrors() : __('Unexpected error (500)', EBBS_LANG_CODE);
                        break;
                    default:
                        $error = __('Unexpected error', EBBS_LANG_CODE);
                }

                //todo:if error occurred -  need call method, which will be delete uploaded files from cloud, because backup data is not full. or try to upload file again

                $response->addError($error);

                $log->string(__(
                    sprintf(
                        'Cannot upload to the "%s": %s',
                        ucfirst($destination),
                        is_array($error) ? array_pop($error) : $error
                    )
                    , EBBS_LANG_CODE));
            }
        } else {
            $response->addError(__('Error! Don\'t exist file: ' . $file, EBBS_LANG_CODE));
        }

        return $response->ajaxExec();
    }

    public function writeTmpDbAction()
    {
        $request = reqEbbs::get('post');

        if (isset($request['tmp'])) {
            $file = frameEbbs::_()->getModule('warehouse')->getTemporaryPath()
                . DIRECTORY_SEPARATOR
                . 'stacks.dat';

            file_put_contents($file, $request['tmp'] . PHP_EOL, FILE_APPEND);
        }
    }

    public function createDBDumpPerStack(){
        $response = new responseEbbs();
        $request = reqEbbs::get('post');
        $stack = !empty($request['stack']) ? $request['stack'] : false;
        $filename = !empty($request['filename']) ? $request['filename'] : false;
        $firstQuery = !empty($request['firstQuery']) ? true : false;
        /** @var backupLogModelEbbs $log */
        $log = $this->getModel('backupLog');

        if(!empty($stack) && !empty($filename)) {
            $result = $this->getModel('database')->create($filename, $stack, $firstQuery);
            if($result){
                $log->string(__('Tables backup complete: ', EBBS_LANG_CODE) . implode(', ', $stack));
            } else {
                $log->string(__('Tables backup failure: ', EBBS_LANG_CODE) . implode(', ', $stack));
            }
        } else {
            $response->addError(__('Error in database.', EBBS_LANG_CODE));
        }

        return $response->ajaxExec();
    }

	/**
	 * Restore Action
	 * Restore system and/or database from backup
	 */
	public function restoreAction() {
		$request  = reqEbbs::get('post');
		$response = new responseEbbs();
		$filename = $request['filename'];
		$model    = $this->getModel();

        // This block for pro-version module 'scrambler'
        $needKeyToDecryptDB = dispatcherEbbs::applyFilters('checkIsNeedSecretKeyToEncryptedDB', false, $filename, $request);
        if($needKeyToDecryptDB){
            $response->addData(array('need' => 'secretKey'));
            return $response->ajaxExec();
        }

		$result = $model->restore($filename);

		if (false === $result) {
            $errors = array_merge($model->getDatabase()->getErrors(), $model->getFilesystem()->getErrors());
            if (empty($errors)) {
                $errors = __('Unable to restore from ' . $filename, EBBS_LANG_CODE);
            }
			$response->addError($errors);
		}
        elseif(is_array($result) && array_key_exists('error', $result)) {
            $response->addError($result['error']);
        }
        elseif(is_array($result) && !empty($result)) {
            $content = __('Unable to restore backup files. Check folder or files writing permissions. Try to set 766 permissions to the:', EBBS_LANG_CODE) . ' <br>'. implode('<br>', $result);
            $response->addError($content);
        }
		else {
			$response->addData($result);
			$response->addMessage(__('Done!', EBBS_LANG_CODE));
		}

        $response->addData(array('result' => $result));
        return $response->ajaxExec();
	}

	/**
	 * Download Action
	 */
	public function downloadAction() {
		$request  = reqEbbs::get('get');
		$filename = $request['download'];

        $file = frameEbbs::_()->getModule('warehouse')->getPath() . DS . $filename;

        if (is_file($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
	}

	/**
	 * Remove Action
	 */
	public function removeAction() {
		$request     = reqEbbs::get('post');
		$response    = new responseEbbs();
		$model       = $this->getModel();

        if(!empty($request['deleteLog'])){
            $logFilename = pathinfo($request['filename']);
            $model->remove($logFilename['filename'].'.txt');
        }

		if ($model->remove($request['filename']) === true) {
			$response->addMessage(__('Backup successfully removed', EBBS_LANG_CODE));
		}
		else {
			$response->addError(__('Unable to delete backup', EBBS_LANG_CODE));
		}

		$response->ajaxExec();
	}

    public function resetAction()
    {
        $request  = reqEbbs::get('post');
        $response = new responseEbbs();

        $this->unlock();

        $response->addMessage(__('Successfully!', EBBS_LANG_CODE));

        return $response->ajaxExec();
    }

    public function saveRestoreSettingAction(){
        $request     = reqEbbs::get('post');
        $response    = new responseEbbs();
        $settingKey = (!empty($request['setting-key'])) ? trim($request['setting-key']) : null;
        $value = (!empty($request['value'])) ? 1 : 0;
        $result = frameEbbs::_()->getTable('options')->update(array('value' => $value), array('code' => $settingKey));

        if($result)
            $response->addMessage(__('Setting saved!', EBBS_LANG_CODE));
        else
            $response->addError(__('Database error, please try again', EBBS_LANG_CODE));

        $response->ajaxExec();
    }

	/**
	 * Get model
	 * @param  string $name
	 * @return \backupModelEbbs
	 */
	public function getModel($name = '') {
		return parent::getModel($name);
	}

	/**
	 *
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function render($template, $data = array()) {
		return $this->getView()->getContent('backup.' . $template, $data);
	}
	public function checkExtensions($res = false) {
		if(!function_exists('gzopen')) {
            $msg = __('There are no zlib extension on your server. This mean that you can make only database backup.<br/>Check this link <a target="_blank" href="http://php.net/manual/en/zlib.installation.php">http://php.net/manual/en/zlib.installation.php</a> or contact your hosting provider and ask them to resolve this issue for you.', EBBS_LANG_CODE);
            if(is_a($res, 'responseEbbs')){
                $res->addError($msg);
                $msg = false;
            }
			return $msg;
		}
		if(!class_exists('ZipArchive')) {
            $msg = __('There are no ZipArchive library on your server. This mean that you can make only database backup.<br/>Check this link <a target="_blank" href="http://php.net/manual/en/book.zip.php">http://php.net/manual/en/book.zip.php</a> or contact your hosting provider and ask them to resolve this issue for you.', EBBS_LANG_CODE);
            if(is_a($res, 'responseEbbs')){
                $res->addError($msg);
                $msg = false;
            }
            return $msg;
		}
		return true;
	}
	public function getPermissions() {
		return array(
			EBBS_USERLEVELS => array(
				EBBS_ADMIN => array('render', 'getModel', 'removeAction', 'downloadAction', 'restoreAction', 'writeTmpDbAction',
					'createAction', 'indexAction', 'getBackupsListContentAjax')
			),
		);
	}

    public function checkProcessAction()
    {
        $response = new responseEbbs();

        $response->addData(
            array(
                'in_process' => frameEbbs::_()->getModule('backup')->isLocked()
            )
        );

        return $response->ajaxExec();
    }

    public function unlockAction()
    {
        $this->unlock();

        $response = new responseEbbs();

        $response->addData(array(
            'success' => true,
        ));

        return $response->ajaxExec();
    }

    /**
     * Disallows to do new backups while backup is doing now.
     */
    public function lock()
    {
        frameEbbs::_()->getModule('backup')->lock();
    }

    /**
     * Allows to do backups.
     */
    public function unlock()
    {
        frameEbbs::_()->getModule('backup')->unlock();
    }

    public function getBackupLog()
    {
        $response = new responseEbbs();
        $request = reqEbbs::get('post');
        /** @var backupTechLogModelEbbs $techLog */
        $techLog = $this->getModel('backupTechLog');
        $techLog->setLogName($request['backupId']);
        /** @var backupLogTxtModelEbbs $log */
        $log = $this->getModel('backupLogTxt');
        $log->setLogName($request['backupId']);
        $backupComplete = $techLog->get('complete');
        $backupMessage = $techLog->get('backupMessage');
        $uploadedPercent = $techLog->get('uploadedPercent');

        $response->addData(
            array(
                'backupLog' => $log->getContent(),
                'backupComplete' => $backupComplete,
                'backupMessage' => $backupMessage,
                'uploadedPercent' => $uploadedPercent,
            )
        );

        if($backupComplete)
            $techLog->deleteOldLogs();

        return $response->ajaxExec();
    }

    public function getBackupsListContentAjax() {
        $response = new responseEbbs();
        $content = $this->getView()->getBackupsListContent();
        $response->addData(array('content' => $content));
        $response->ajaxExec();
    }
}
