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

	/**
	 * Create Action
	 * Create new backup
	 */
	public function createAction() {
        $request = reqEbbs::get('post');
        $response = new responseEbbs();

        /** @var backupLogModelEbbs $log */
        $log = $this->getModel('backupLog');

        if(!empty($request['opt_values'])){
            // clear previous log data
            $log->clear();
            do_action('bupBeforeSaveBackupSettings', $request['opt_values']);
            $log->writeBackupSettings($request['opt_values']);
            /* @var $optionsModel optionsModelEbbs*/
            $optionsModel = frameEbbs::_()->getModule('options')->getModel();
            $optionsModel->saveMainFromDestGroup($request);
            $optionsModel->saveGroup($request);
            $optionsModel->refreshOptions();

            // if warehouse changed - create necessary dir
            $bupFolder = frameEbbs::_()->getModule('warehouse');
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

        $filename = $this->getModel()->generateFilename(array('zip', 'sql', 'txt'));

        if ($this->getModel()->isFilesystemRequired() && empty($request['filesBackupComplete'])) {
            if(!empty($request['opt_values']))
                $log->saveBackupDirSetting($request['opt_values']);
            if (!isset($request['complete'])) {
                // Disallow to do backups while backup already in proccess.
                $this->lock();

                $files = $this->getModel()->getFilesList();
                // $files = array_map('realpath', $files);

                $log->string(sprintf('%s files scanned.', count($files)));

                $warehouse = frameEbbs::_()->getModule('warehouse')->getPath();
                $dir = frameEbbs::_()->getModule('warehouse')->getTemporaryPath();

                $log->string(__('Clear out old temporary files', EBBS_LANG_CODE));
                if (file_exists($file = $dir . '/stacks.dat')) {
                    if (@unlink($file)) {
                        $log->string(__(sprintf('%s successfully deleted', basename($file)), EBBS_LANG_CODE));
                    } else {
                        $log->string(__(sprintf('Cannot delete file %s. If you notice a problem with archives - delete the file manually', $file), EBBS_LANG_CODE));
                    }
                }
                $tmpDirFiles = glob($dir . '/*');
                if(!empty($tmpDirFiles) && is_array($tmpDirFiles)) {
                    foreach ($tmpDirFiles as $tmp) {
                        if (substr(basename($tmp), 0, 3) === 'BUP') {
                            if (@unlink($tmp)) {
                                $log->string(__(sprintf('%s successfully deleted', $tmp), EBBS_LANG_CODE));
                            } else {
                                $log->string(__(sprintf('Cannot delete file %s', $tmp), EBBS_LANG_CODE));
                            }
                        }
                    }
                }

                $response->addData(array(
                    'files'     => $files,
                    'per_stack' => EBBS_FILES_PER_STACK,
                ));

                $log->string(__('Send request to generate temporary file stacks', EBBS_LANG_CODE));

                return $response->ajaxExec();
            }

            $log->string(__(sprintf('Create a backup of the file system: %s', $filename['zip']), EBBS_LANG_CODE));
            $this->getModel()->getFilesystem()->create($filename['zip']);

            $log->setCurrentBackupFilesName($filename['zip']);
        }

        if ($this->getModel()->isDatabaseRequired()) {
            if(empty($request['databaseBackupComplete'])) {
                // Disallow to do backups while backup already in proccess.
                $this->lock();

                $log->string(__(sprintf('Create a backup of the database: %s', $filename['sql']), EBBS_LANG_CODE));

                if ($this->_tablesPerStack > 0) {
                    $tables = $this->getModel()->getDatabase()->getTablesName();
                    $response->addData(
                        array(
                            'dbDumpFileName' => json_encode($filename['sql']),
                            'tables' => $tables,
                            'per_stack' => $this->_tablesPerStack
                        )
                    );

                    $log->string(__('Send requests to getting database tables name.', EBBS_LANG_CODE));

                    $log->setCurrentBackupFilesName($filename['sql']);

                    return $response->ajaxExec();
                } else {
                    $this->getModel()->getDatabase()->create($filename['sql']);
                    $dbErrors = $this->getModel()->getDatabase()->getErrors();
                    if (!empty($dbErrors)) {
                        $log->string(__(sprintf('Errors during creation of database backup, errors count %d', count($dbErrors)), EBBS_LANG_CODE));
                        $response->addError($dbErrors);
                        return $response->ajaxExec();
                    }
                    $log->setCurrentBackupFilesName($filename['sql']);
                }
            } else {
                $log->string(__('Database backup complete.'), EBBS_LANG_CODE);
            }
        }
        $cloud = $log->getCurrentBackupFilesName();
        $log->string(__('Backup complete', EBBS_LANG_CODE));

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

        if (frameEbbs::_()->getModule('options')->get('email_ch') == 1) {
            $email = frameEbbs::_()->getModule('options')->get('email');
            $subject = __('Backup by Supsystic Notifications', EBBS_LANG_CODE);

            $log->string(__('Email notification required.', EBBS_LANG_CODE));
            $log->string(sprintf(__('Sending to', EBBS_LANG_CODE) . '%s', $email));

            $message = $log->getContents();

            wp_mail($email, $subject, $message);
        }

        $backupPath =  untrailingslashit(frameEbbs::_()->getModule('warehouse')->getPath()) . DS;
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

    /**
     * Create Stack Action
     * Creates stacks of files with EBBS_FILER_PER_STACK files limit and returns temporary file name
     */
    public function createStackAction() {
		@set_time_limit(0);

        $request = reqEbbs::get('post');
        $response = new responseEbbs();

        /** @var backupLogModelEbbs $log */
        $log = $this->getModel('backupLog');

        if (!isset($request['files'])) {
            return;
        }

        $log->string(__(sprintf('Trying to generate a stack of %s files', count($request['files'])), EBBS_LANG_CODE));

        $filesystem = $this->getModel()->getFilesystem();
        $filename = $filesystem->getTemporaryArchive($request['files']);
        if(frameEbbs::_()->getModule('options')->get('warehouse_abs') == 1){
            $absPath = str_replace('/', DS, ABSPATH);
            $filename = str_replace('/', DS, $filename);
            $filename = str_replace($absPath, '', $filename);
        }

        if ($filename === null) {
            $log->string(__('Unable to create the temporary archive', EBBS_LANG_CODE));
            $response->addError(__('Unable to create the temporary archive', EBBS_LANG_CODE));
        } else {
            $log->string(__(sprintf('Temporary stack %s successfully generated', $filename), EBBS_LANG_CODE));
            $response->addData(array('filename' => $filename));
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
	public function checkExtensions($res=false) {
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
					'createStackAction', 'createAction', 'indexAction')
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

        $response->addData(
            array(
                'backupLog' => frameEbbs::_()->getModule('backup')->getModel('backupLog')->getBackupLog(),
            )
        );

        return $response->ajaxExec();
    }
}
