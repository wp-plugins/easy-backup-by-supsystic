<?php
class adminmenuEbbs extends moduleEbbs {
    public function init() {
        parent::init();
        $this->getController()->getView('adminmenu')->init();
		$plugName = plugin_basename(EBBS_DIR. EBBS_MAIN_FILE);
		add_filter('plugin_action_links_'. $plugName, array($this, 'addSettingsLinkForPlug') );
    }
	public function addSettingsLinkForPlug($links) {
		array_unshift($links, '<a href="'. uriEbbs::_(array('baseUrl' => admin_url('admin.php'), 'page' => plugin_basename(frameEbbs::_()->getModule('adminmenu')->getView()->getFile()))). '">'. __('Settings', EBBS_LANG_CODE). '</a>');
		return $links;
	}
	public function getMainLink() {
		return uriEbbs::_(array('baseUrl' => admin_url('admin.php'), 'page' => $this->getView()->getFile()));
	}
}

