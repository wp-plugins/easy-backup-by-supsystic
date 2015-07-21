<?php
class adminmenuViewEbbs extends viewEbbs {
    protected $_file = '';

    public function init() {
        $this->_file = EBBS_PLUGIN_PAGE_URL_SUFFIX;
        add_action('admin_menu', array($this, 'initMenu'), 9);
        parent::init();
    }
    public function initMenu() {
		$accessCap = 'manage_options';
        add_menu_page(
            __(EBBS_S_WP_PLUGIN_NAME, EBBS_LANG_CODE),
            __(EBBS_S_WP_PLUGIN_NAME, EBBS_LANG_CODE),
            $accessCap,
            $this->_file,
            array(frameEbbs::_()->getModule('options')->getView(), 'getAdminPage'),
            'dashicons-cloud'
        );
    }
    public function getFile() {
        return $this->_file;
    }
}