<?php
class logViewEbbs extends viewEbbs {
	public function getAdminOptions() {
		frameEbbs::_()->addScript('adminLogOptions', $this->getModule()->getModPath(). 'js/admin.log.js');
		return parent::getContent('logPage');
	}
	
	public function getBlockLogEbbs($key, $files) {
		$sendTplData = array();
		$title = $this->getModel()->getDateLog($key);
		$header = 'Backup log '.$title;
		$sendTplData = array( $key, $header, $files );
		$this->assign('logData', $sendTplData);

		$ret = parent::getContent('logBlock');
			
		return $ret;
	}
	
}