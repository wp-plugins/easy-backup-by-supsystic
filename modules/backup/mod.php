<?php

/**
 * Backup Module for Supsystic Backup plugin
 * @package SupsysticBackup\Modules\Backup
 * @version 2.0
 */
class backupEbbs extends moduleEbbs {

	/**
	 * Path to libraries
	 * @var string
	 */
	private $_librariesPath = 'classes';

	/**
	 * Libraries list
	 * @var array
	 */
	private $_libraries = array(

        /* uses for creating archives */
		'zip' => array(
            'filename'  => 'Zip.php',
            'classname' => 'Zip',
        ),

        /* uses for unpacking archives */
        'pcl' => array(
            'filename'  => 'pclzip.lib.php',
            'classname' => 'PclZip',
        ),
	);

	/**
	 * Plugin initialization
	 */
	public function init() {
		parent::init();

        /* Register tab */
		dispatcherEbbs::addFilter('adminOptionsTabs', array($this, 'registerModuleTab'));
        dispatcherEbbs::addfilter('adminGetUploadedFiles', array($this, 'getUploadedFiles'));
        dispatcherEbbs::addfilter('getBackupDestination', array($this, 'addLocalFTPEbbsDestination'));

        /* Load assets */
        $this->loadModuleScripts();

        $this->loadLibrary('pcl');

		/* Force run download action if $_GET param setted */
		if (isset($_GET['download']) && !empty($_GET['download'])) {
			$this->run('downloadAction');
		}
	}

	/**
	 * Loading dependencies and module classes
     * @param string $handle
	 */
	public function loadLibrary($handle)
    {
        if (isset($this->_libraries[$handle])) {
            $library = $this->_libraries[$handle];

            if ('pcl' === strtolower($handle)) {
                $this->loadPcl();
                return;
            }

            if (!class_exists($library['classname'])) {
                require_once realpath($this->getModDir()) . '/classes/' . $library['filename'];
            }
        }
	}

    protected function loadPcl()
    {
        if (is_file($file = ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            require_once $file;
            return;
        }

        require_once realpath($this->getModDir()) . '/classes/pclzip.lib.php';
    }

	/**
	 * Load javascript & css files
	 */
	public function loadModuleScripts() {
		if (is_admin() && frameEbbs::_()->isPluginAdminPage()) {
			frameEbbs::_()->addScript('adminBackupOptionsV2', $this->getModPath() . 'js/admin.backup.v2.js');
		}
	}

	/**
	 * Add tab to the menu
	 */
	public function registerModuleTab($tabs) {
		$tabs['bupLog'] = array(
			'title'   => __('Restore', EBBS_LANG_CODE),
			'content' => array($this->getController(), 'indexAction'),
            'faIcon' => 'fa-database',
            'sort_order' => 20,
		);


		return $tabs;
	}

    /**
     * Run controller's action
     * @param string $action
     * @return mixed
     */
    public function run($action) {
        $controller = $this->getController();
        if(method_exists($controller, $action)) {
            return $controller->{$action}();
        }
    }

    /**
     * Returns path to the temporary folder.
     *
     * @return string
     */
    public function getWarehouseTmp()
    {
		return frameEbbs::_()->getModule('warehouse')->getTemporaryPath();
    }

	/**
	* Disallows to do new backups while backup is doing now.
	*/
	public function lock()
	{
		if (!defined('EBBS_LOCK_FIELD')) {
			return;
		}

		update_option(EBBS_LOCK_FIELD, 1);
	}

	/**
	* Allows to do backups.
	*/
	public function unlock()
	{
		if (!defined('EBBS_LOCK_FIELD')) {
			return;
		}

		update_option(EBBS_LOCK_FIELD, 0);
	}

	public function isLocked()
	{
		if (!defined('EBBS_LOCK_FIELD')) {
			return false;
		}

		if (get_option(EBBS_LOCK_FIELD) == 0) {
			return false;
		}

		return true;
	}

    /**
     * Register uploaded files to backups page
     *
     * @param  array $files
     * @return array
     */
    public function getUploadedFiles($files) {
        $uploadedFiles = $this->getController()->getModel()->getBackupsList();
        if(is_array($uploadedFiles)){
            foreach($uploadedFiles as $key=>$file){
                $files[$key] = $file;
            }
        }
        return $files;
    }

    public function addLocalFTPEbbsDestination($tabs){
        $tabs[] = array(
            'title'   => __('Local Backup', EBBS_LANG_CODE),
            'faIcon' => 'fa-server',
            'sortNum' => 1,
            'key' => 'ftp',
        );

        return $tabs;
    }
}
