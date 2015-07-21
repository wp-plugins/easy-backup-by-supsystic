<?php
class optionsControllerEbbs extends controllerEbbs {
	
	public function saveGroup() {
		$res = new responseEbbs();
		
		$post = reqEbbs::get('post');
		
		if ($result = $this->getModel()->saveGroup($post)) {
			$res->addMessage(__('Save Complete', EBBS_LANG_CODE));
			$res->addData($result);
		} else 
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	
	public function saveMainFromDestGroup(){
		$res = new responseEbbs();
		$post = reqEbbs::get('post');
		if ($this->getModel()->saveMainFromDestGroup($post) && $this->getModel()->saveGroup($post)) {
			$res->addMessage(__('Save Complete', EBBS_LANG_CODE));
			$res->addData(true);
		} else 
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			EBBS_USERLEVELS => array(
				EBBS_ADMIN => array('saveGroup', 'saveMainFromDestGroup')
			),
		);
	}
}

