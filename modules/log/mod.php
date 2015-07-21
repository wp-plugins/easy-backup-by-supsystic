<?php
class logEbbs extends moduleEbbs {
	
	/**
	 * Plugin initialization
	 */
    public function init() {
		parent::init();
		
		//dispatcherEbbs::addFilter('adminOptionsTabs', array($this, 'registerTab'));
	}
	
	/**
	 * Module tab registration
	 * @param  array $tabs
	 * @return array
	 */
	public function registerTab($tabs) {
		$tabs['bupLogOptions'] = array(
			'title'   => __('Restore', EBBS_LANG_CODE),
			'content' => $this->run('indexAction'),
            'faIcon' => 'fa-file-text',
		);
		
		return $tabs;
	}
	
	public function run($action) {
		$controller = $this->getController();
		if (method_exists($controller, $action)) {
			return $controller->$action();
		}
	}
}