<?php
class templateViewEbbs extends viewEbbs {
	protected $_styles = array();
	protected $_scripts = array();
	/**
	 * Provide or not html code of subscribe for to template. Can be re-defined for child classes
	 */
	protected $_useSubscribeForm = true;
	/**
	 * Provide or not html code of social icons for to template. Can be re-defined for child classes
	 */
	protected $_useSocIcons = true;
	public function getComingSoonPageHtml() {
		$this->_beforeShow();
		
		$this->assign('msgTitle', frameEbbs::_()->getModule('options')->get('msg_title'));
		$this->assign('msgTitleColor', frameEbbs::_()->getModule('options')->get('msg_title_color'));
		$this->assign('msgTitleFont', frameEbbs::_()->getModule('options')->get('msg_title_font'));
		$msgTitleStyle = array();
		if(!empty($this->msgTitleColor))
			$msgTitleStyle['color'] = $this->msgTitleColor;
		if(!empty($this->msgTitleFont)) {
			$msgTitleStyle['font-family'] = $this->msgTitleFont;
			$this->_styles[] = 'http://fonts.googleapis.com/css?family='. $this->msgTitleFont. '&subset=latin,cyrillic-ext';
		}
		$this->assign('msgTitleStyle', utilsEbbs::arrToCss( $msgTitleStyle ));
		
		$this->assign('msgText', frameEbbs::_()->getModule('options')->get('msg_text'));
		$this->assign('msgTextColor', frameEbbs::_()->getModule('options')->get('msg_text_color'));
		$this->assign('msgTextFont', frameEbbs::_()->getModule('options')->get('msg_text_font'));
		$msgTextStyle = array();
		if(!empty($this->msgTextColor))
			$msgTextStyle['color'] = $this->msgTextColor;
		if(!empty($this->msgTextFont)) {
			$msgTextStyle['font-family'] = $this->msgTextFont;
			if($this->msgTitleFont != $this->msgTextFont)
				$this->_styles[] = 'http://fonts.googleapis.com/css?family='. $this->msgTextFont. '&subset=latin,cyrillic-ext';
		}
		$this->assign('msgTextStyle', utilsEbbs::arrToCss( $msgTextStyle ));
		
		if($this->_useSubscribeForm && frameEbbs::_()->getModule('options')->get('sub_enable')) {
			$this->_scripts[] = frameEbbs::_()->getModule('subscribe')->getModPath(). 'js/frontend.subscribe.js';
			$this->assign('subscribeForm', frameEbbs::_()->getModule('subscribe')->getController()->getView()->getUserForm());
		}
		if($this->_useSocIcons) {
			$this->assign('socIcons', frameEbbs::_()->getModule('social_icons')->getController()->getView()->getFrontendContent());
		}
		
		if(file_exists($this->getModule()->getModDir(). 'css/style.css'))
			$this->_styles[] = $this->getModule()->getModPath(). 'css/style.css';
		
		$this->assign('logoPath', $this->getModule()->getLogoImgPath());
		$this->assign('bgCssAttrs', dispatcherEbbs::applyFilters('tplBgCssAttrs', $this->getModule()->getBgCssAttrs()));
		$this->assign('styles', dispatcherEbbs::applyFilters('tplStyles', $this->_styles));
		$this->assign('scripts', dispatcherEbbs::applyFilters('tplScripts', $this->_scripts));
		$this->assign('initJsVars', dispatcherEbbs::applyFilters('tplInitJsVars', $this->initJsVars()));
		$this->assign('messages', frameEbbs::_()->getRes()->getMessages());
		$this->assign('errors', frameEbbs::_()->getRes()->getErrors());
		return parent::getContent($this->getCode(). 'BUPHtml');
	}
	public function addScript($path) {
		if(!in_array($path, $this->_scripts))
			$this->_scripts[] = $path;
	}
	public function addStyle($path) {
		if(!in_array($path, $this->_styles))
			$this->_styles[] = $path;
	}
	public function initJsVars() {
		$ajaxurl = admin_url('admin-ajax.php');
		if(frameEbbs::_()->getModule('options')->get('ssl_on_ajax')) {
			$ajaxurl = uriEbbs::makeHttps($ajaxurl);
		}
		$jsData = array(
			'siteUrl'					=> EBBS_SITE_URL,
			'imgPath'					=> EBBS_IMG_PATH,
			'loader'					=> EBBS_LOADER_IMG,
			'close'						=> EBBS_IMG_PATH. 'cross.gif',
			'ajaxurl'					=> $ajaxurl,
			'animationSpeed'			=> frameEbbs::_()->getModule('options')->get('js_animation_speed'),
			'EBBS_CODE'					=> EBBS_CODE,
		);
		return '<script type="text/javascript">
		// <!--
			var EBBS_DATA = '. utilsEbbs::jsonEncode($jsData). ';
		// -->
		</script>';
	}
	protected function _beforeShow() {
		
	}
}