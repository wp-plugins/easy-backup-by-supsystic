<?php
class logControllerEbbs extends controllerEbbs {
	
	public function indexAction() {
		return $this->render('index', array(
			'files' => $this->getModel()->getFilesList(),
		));
	}
	
	public function deleteAction() {
		
	}
	
	/**
	 * Get model
	 * @param  string $name
	 * @return \logModelEbbs
	 */
	public function getModel($name = '') {
		return parent::getModel($name);
	}
	
	/**
	 * Render view file
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function render($template, $data = array()) {
		return $this->getView()->getContent('log.' . $template, $data);
	}
	public function getPermissions() {
		return array(
			EBBS_USERLEVELS => array(
				EBBS_ADMIN => array('indexAction', 'deleteAction', 'getModel', 'render')
			),
		);
	}
}