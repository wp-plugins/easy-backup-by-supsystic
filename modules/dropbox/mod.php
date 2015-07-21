<?php

/**
 * Dropbox Module for Backup By Supsystic
 * @package BackupBySupsystic\Modules\Dropbox
 * @version 1.0
 */
class dropboxEbbs extends moduleEbbs {

	/**
	 * Module configurations
	 * @since 1.0
	 * @var   array
	 */
	private $config = array(
		'tabs' => array(
			'key'    => 'bupDropboxOptions',
			'title'  => 'DropBox',
			'action' => 'indexAction',
		),
		'storage' => array(
			'label'    => 'DropBox',
			'provider' => 'dropbox',
			'action'   => 'uploadAction',
		),
	);

	/**
	 * Database options
	 * @since 1.0
	 * @var   string
	 */
	private $options = array(
		array(
			'code'        => 'dropbox_app_key',
			'value'       => 'dpzwg762n7tgvt3',
			'label'       => 'Dropbox Application Key',
			'description' => 'Dropbox Application Key',
		),
		array(
			'code'        => 'dropbox_app_secret',
			'value'       => 'vyp3moe0d1sx0s6',
			'label'       => 'Dropbox Application Secret',
			'description' => 'Dropbox Application Secret',
		),
		// Deprecated
        array(
            'code'        => 'dropbox_auth_url',
            'value'       => 'http://supsystic.com/authenticator/index.php/authenticator/dropbox/',
            'label'       => 'Authenticator URL',
            'description' => 'URL to authenticate user with out authenticator',
        ),
        array(
            'code'        => 'dropbox_model',
            'value'       => '',
            'label'       => 'Dropbox model',
            'description' => 'Module uses two models: for PHP 5.2.x and for PHP >= 5.3.x',
        ),
	);

	/**
	 * Relative path to Dropbox SDK
	 */
	private $sdkPath = 'sdk/';

	/**
	 * Module supported row
	 */
	private $_isSupportedModule = false;

	/**
	 * Module installer
	 *
	 * @since  1.0
	 */
	public function install() {
		parent::install();

		foreach($this->options as $options) {
			frameEbbs::_()->getTable('options')->insert($options);
		}
	}

	/**
	 * Module initialization
	 *
	 * @since  1.0
	 */
	public function init() {
		parent::init();
        if (!extension_loaded('curl')) {
            dispatcherEbbs::addFilter('getBackupDestination', array($this, 'registerNotSupport'));
            return;
        }

        $curl = curl_version();
        $this->_isSupportedModule = true;

//		if((version_compare(PHP_VERSION, '5.3.1', '>=') &&
//				(substr($curl['ssl_version'], 0, 3) != 'NSS')) && PHP_INT_MAX > 2147483647)
//		{
//			require $this->sdkPath . 'autoload.php';
//
//            require dirname(__FILE__) . '/classes/curlEbbs.php';
//            frameEbbs::_()->getModule('options')->set('dropbox', 'dropbox_model');
//        }
//		else {
            require dirname(__FILE__) . '/classes/curlEbbs.php';
//            $this->getController()->modelType = 'dropbox52';
//            frameEbbs::_()->getModule('options')->set('dropbox52', 'dropbox_model');
//		}

        frameEbbs::_()->getModule('options')->set('dropbox52', 'dropbox_model');

        if(is_admin() && frameEbbs::_()->isPluginAdminPage()) {
            frameEbbs::_()->addScript('adminDropboxOptions', $this->getModPath() . 'js/admin.dropbox.js');
        }


        dispatcherEbbs::addFilter('getBackupDestination', array($this, 'addDropboxEbbsDestination'));
        dispatcherEbbs::addFilter('adminSendToLinks', array($this, 'registerSendLink'));
        dispatcherEbbs::addfilter('adminBackupUpload', array($this, 'registerUploadMethod'));
        dispatcherEbbs::addfilter('adminGetUploadedFiles', array($this, 'getUploadedFiles'));

	}

	public function registerNotSupport($tabs) {
		$tabs[] = array(
			'title'   => $this->config['tabs']['title'],
			'content' => __('Your server does not support the Dropbox without cURL extension', EBBS_LANG_CODE),
            'faIcon' => 'fa-dropbox',
            'sortNum' => 2,
            'key' => 'dropbox',
		);

		return $tabs;
	}

	/**
	 * Register Dropbox tab in plugin menu
	 *
	 * @since  1.0
	 * @param  array $tabs
	 * @return array
	 */
	public function addDropboxEbbsDestination($tabs) {
		$tabs[] = array(
			'title'   => $this->config['tabs']['title'],
			'content' => $this->run($this->config['tabs']['action']),
            'faIcon' => 'fa-dropbox',
            'sortNum' => 2,
            'key' => 'dropbox',
		);

		return $tabs;
	}

	/**
	 * Register "Send to" link to storage module
	 *
	 * @since  1.0
	 * @param  array $providers
	 * @return array
	 */
	public function registerSendLink($providers) {
		array_push($providers, $this->config['storage']);
		return $providers;
	}

	/**
	 * Add Dropbox handler to backup module
	 * @param  array $methods
	 * @return string
	 */
	public function registerUploadMethod($methods) {
        $model = frameEbbs::_()->getModule('options')->get('dropbox_model');
		$methods['dropbox'] = array($this->getController()->getModel($model), 'upload');
		return $methods;
	}

    /**
     * Run controller's action
     *
     * @since  1.0
     * @param  string $action
     * @return mixed
     */
    public function run($action) {
        $controller = $this->getController();
        if(method_exists($controller, $action)) {
            return $controller->{$action}();
        }
    }

    /**
     * Register uploaded files to backups page
     *
     * @param  array $files
     * @return array
     */
    public function getUploadedFiles($files) {
        if($this->_isSupportedModule){
            try{
                $uploadedFiles = $this->getController()->model->getUploadedFiles();
                if(is_array($uploadedFiles['contents'])){
                    foreach($uploadedFiles['contents'] as $key=>$file){
                        $files[$key] = $file;
                    }
                }
            } catch (Exception $e) {}
        }
        return $files;
    }
}
