<?php

/**
 * Dropbox Module Controller
 *
 * @package BackupBySupsystic\Modules\Dropbox
 * @version 1.2
 */
class dropboxControllerEbbs extends controllerEbbs {

	/**
	 * Instance of Dropbox model
	 * @var \dropboxModelEbbs
	 */
	public $model = null;

    public $modelType = null;

    public function getModel($name = ''){
        if (empty($name) || $name == 'dropbox')
            $name = 'dropbox52';
        return parent::getModel($name);
    }

	/**
	 * Prefix for view files
	 * @var string
	 */
	protected $templatePrefix = 'dropbox';

    public function __construct($code = '') {
        parent::__construct($code);

        $model = frameEbbs::_()->getModule('options')->get('dropbox_model');
        $this->model = $this->getModel($model);
    }

	/**
	 * Index Action
	 *
	 * @since  1.0
	 */
	public function indexAction() {

		if($this->model->isAuthenticated() === false) {
			return $this->authenticateAction();
		}
		try {
			$tabHtml = $this->render('index', array(
				'info'  => $this->model->getQuota(),
			));
		} catch(RuntimeException $e) {
			return $this->authenticateAction(array('errors' => $e->getMessage()));
		} catch (Exception $e) {
			return $this->authenticateAction(array('errors' => $e->getMessage()));
		}
		return $tabHtml;
	}

	/**
	 * Authenticate Action
	 *
	 * @since  1.0
	 */
	public function authenticateAction($errors = array()) {
		$request = reqEbbs::get('get');

        if (EBBS_PLUGIN_PAGE_URL_SUFFIX !== $request['page']) {
            return;
        }

		if(!isset($request['dropboxToken'])) {
			$url  = 'http://supsystic.com/authenticator/index.php/authenticator/dropbox';
			$slug = frameEbbs::_()->getModule('adminmenu')->getView()->getFile();
            $queryString = !empty($_SERVER['QUERY_STRING']) ? 'admin.php?' . $_SERVER['QUERY_STRING'] : '';
            $redirectURI = !empty($queryString) ? $queryString : 'admin.php?page=' . $slug;
            if(!empty($errors) && !is_array($errors))
				$errors = array($errors);
			return $this->render('auth', array(
                'authUrl' => $url . '?ref=' . base64_encode(admin_url($redirectURI)),
				'errors' => $errors,
            ));
		}
		else {
			$authResult = $this->model->authenticate($request['dropboxToken']);

			if($authResult === false) {
				return $this->model->getErrors();
			}
			else {
                $uri = null;
                if(is_array($request)){
                    $uri = array();
                    foreach($request as $key => $value){
                        if($key != 'dropboxToken')
                            $uri[] = $key . '=' . $value;
                    }
                    $uri = 'admin.php?' . join('&', $uri);
                }
                $redirectURI = !empty($uri) ? $uri : 'admin.php?page='.EBBS_PLUGIN_PAGE_URL_SUFFIX;
                redirectEbbs(admin_url($redirectURI));
			}
		}
	}

    /**
     * Logout Action
     *
     * @since 1.2
     */
    public function logoutAction() {
		$response = new responseEbbs();

		session_destroy();
        $this->model->removeToken();

		$response->addMessage(__('Please, wait...', EBBS_LANG_CODE));
		$response->ajaxExec();
	}

	/**
	 * Not Support Action
	 *
	 * @since  1.0
	 */
	public function notSupportAction() {
		$curl = curl_version();

		$messages = array(
			__('Your server not meet the requirements Dropbox SDK' . PHP_EOL, EBBS_LANG_CODE),
			__(sprintf('Your PHP version: %s (5.3.1 or higher required)', PHP_VERSION), EBBS_LANG_CODE),
			__(sprintf('cURL extension: %s (cURL extension is required)', extension_loaded('curl') ? 'installed' : 'not installed'), EBBS_LANG_CODE),
			__(sprintf('cURL SSL version: %s (OpenSSL is required)', $curl['ssl_version']), EBBS_LANG_CODE),
		);

		return $this->render('notSupport', array(
			'messages' => nl2br(implode(PHP_EOL, $messages)),
		));
	}

	/**
	 * Upload Action
	 *
	 * @since  1.0
	 */
	public function uploadAction($files = array()) {
        $request  = reqEbbs::get('post');
		$response = new responseEbbs();
		$stack    = array();

		if(!empty($files)) {
			$stack = array_merge($stack, $files);
		}

		if(isset($request['sendArr']) && !empty($request['sendArr'])) {
			if(!is_array($request['sendArr'])) {
				$request['sendArr'] = explode(',', $request['sendArr']);
			}
		}

		$stack = array_merge($stack, $request['sendArr']);

		$result = $this->model->upload($stack);

		switch($result) {
			case 200:
				$response->addMessage(__('Done!', EBBS_LANG_CODE));
				break;
			case 401:
				$response->addError(__('Authentication required', EBBS_LANG_CODE));
				break;
			case 404:
				$response->addError(__('Nothing to upload', EBBS_LANG_CODE));
				break;
			case 500:
				$response->addError($this->model->getErrors());
				break;
			default:
				$response->addMessage($result);
		}

		return $response->ajaxExec();
	}

	/**
	 * Delete Action
	 *
	 * @since  1.0
	 */
	public function deleteAction() {
		$request  = reqEbbs::get('post');
		$response = new responseEbbs();

        if(!empty($request['deleteLog'])){
            $model    = frameEbbs::_()->getModule('backup')->getModel();
            $logFilename = pathinfo($request['file']);
            $model->remove($logFilename['filename'].'.txt');
        }

		if(!isset($request['file']) OR empty($request['file'])) {
			$response->addError(__('Nothing to delete', EBBS_LANG_CODE));
		}

		if($this->model->remove($request['file']) === true) {
			$response->addMessage(__('File successfully deleted', EBBS_LANG_CODE));
		}
		else {
			$response->addError($this->model->getErrors());
		}

		$response->ajaxExec();
	}

	/**
	 * Restore Action
	 *
	 * @since  1.0
	 */
	public function restoreAction() {
		$request  = reqEbbs::get('post');
		$response = new responseEbbs();

		if(!isset($request['file']) OR empty($request['file'])) {
			$response->addError(__('There was an error during recovery', EBBS_LANG_CODE));
		}

        $extension = pathinfo($request['file'], PATHINFO_EXTENSION);

        if(!$extension) {
            $stacksLocalFolder = frameEbbs::_()->getModule('warehouse')->getPath() . DS . basename($request['file']) . DS;

            if(!file_exists($stacksLocalFolder))
                frameEbbs::_()->getModule('warehouse')->getController()->getModel('warehouse')->create($stacksLocalFolder);

            $backupStacksList = $this->model->getUploadedFiles(basename($request['file']) . '/');

//            if(!empty($backupStacksList)) {
//                $response->addMessage(__('Downloading from DropBox - 0%', EBBS_LANG_CODE));
//                $response->addData(array('stacksList' => $backupStacksList));
//            } else {
//                $response->addError(__('Stacks of backup don\'t find!', EBBS_LANG_CODE));
//            }

//            return $response->ajaxExec();
        } else {
            $backupStacksList[] = $request['file'];
        }

        if(!empty($backupStacksList)){
            foreach($backupStacksList as $file) {
                if(!file_exists(frameEbbs::_()->getModule('backup')->getController()->getModel('backup')->getConfig('warehouse') . $file)) {
                    if ($this->model->download($file) === true) {
//                        $response->addData(array('filename' => $file));
                    } else {
                        $response->addError($this->model->getErrors());
                    }
                }
            }
            if(!$extension) {
                $response->addData(array('filename' => pathinfo($file, PATHINFO_DIRNAME)));
            } else {
                $response->addData(array('filename' => $request['file']));
            }
        }

		return $response->ajaxExec();
	}

	/**
	 * Render view file
	 *
	 * @since  1.0
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function render($template, $data = array()) {
		return $this->getView()->getContent($this->templatePrefix . '.' . $template, $data);
	}
}
