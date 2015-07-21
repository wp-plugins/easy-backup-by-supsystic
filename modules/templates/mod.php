<?php
class templatesEbbs extends moduleEbbs {
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    protected $_styles = array();
    public function getTabs(){
        $tabs = array();
        $tab = new tabEbbs(__('Templates', EBBS_LANG_CODE), $this->getCode());
        $tab->setView('templatesTab');
		$tab->setSortOrder(1);
        $tabs[] = $tab;
        return $tabs;
    }
    public function init() {
		if (is_admin() && frameEbbs::_()->isPluginAdminPage()) {
			$this->_styles = array(
				'styleEbbs'				=> array('path' => EBBS_CSS_PATH. 'style.css'),
				'adminStylesEbbs'		=> array('path' => EBBS_CSS_PATH. 'adminStyles.css'),
                'supsystic-uiEbbs'	    => array('path' => EBBS_CSS_PATH. 'supsystic-ui.css'),
                'font-awesomeEbbs'	    => array('path' => EBBS_CSS_PATH. 'font-awesome.css'),
				'jquery-tabs'			=> array('path' => EBBS_CSS_PATH. 'jquery-tabs.css'),
				'jquery-buttons'		=> array('path' => EBBS_CSS_PATH. 'jquery-buttons.css'),
				'bootstrap.min'		=> array('path' => EBBS_CSS_PATH. 'bootstrap.min.css'),
                'icheck'			    => array('path' => EBBS_CSS_PATH. 'jquery.icheck.css', 'for' => 'admin'),
                'tooltipster'		    => array('path' => EBBS_CSS_PATH. 'tooltipster.css', 'for' => 'admin'),
				'wp-jquery-ui-dialog'	=> array(),
				'farbtastic'			=> array(),
				// Our corrections for ui dialog
				'jquery-dialog'			=> array('path' => EBBS_CSS_PATH. 'jquery-dialog.css'),
				'jquery-progress'			=> array('path' => EBBS_CSS_PATH. 'jquery-progress.css'),
			);
			$defaultPlugTheme = frameEbbs::_()->getModule('options')->get('default_theme');
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
				'siteLang'					=> langEbbs::getData(),
				'options'					=> frameEbbs::_()->getModule('options')->getByCode(),
				'EBBS_CODE'					=> EBBS_CODE,
			);
			$jsData = dispatcherEbbs::applyFilters('jsInitVariables', $jsData);

			frameEbbs::_()->addScript('jquery');
			frameEbbs::_()->addScript('jquery-ui-tabs', '', array('jquery'));
			frameEbbs::_()->addScript('jquery-ui-dialog', '', array('jquery'));
			frameEbbs::_()->addScript('jquery-ui-button', '', array('jquery'));

			frameEbbs::_()->addScript('farbtastic');

			frameEbbs::_()->addScript('commonEbbs', EBBS_JS_PATH. 'common.js');
			frameEbbs::_()->addScript('coreEbbs', EBBS_JS_PATH. 'core.js');
            frameEbbs::_()->addScript('icheck', EBBS_JS_PATH. 'icheck.min.js');
            frameEbbs::_()->addScript('tooltipster', EBBS_JS_PATH. 'jquery.tooltipster.min.js');

            frameEbbs::_()->addScript('adminOptionsEbbs', EBBS_JS_PATH. 'admin.options.js', array(), false, true);
            frameEbbs::_()->addScript('ajaxupload', EBBS_JS_PATH. 'ajaxupload.js');
            frameEbbs::_()->addScript('postbox', get_bloginfo('wpurl'). '/wp-admin/js/postbox.js');

			frameEbbs::_()->addJSVar('coreEbbs', 'EBBS_DATA', $jsData);

			/*$desktop = true;
			if(utilsEbbs::isTablet()) {
				$this->_styles['style-tablet'] = array();
				$desktop = false;
			} elseif(utilsEbbs::isMobile()) {
				$this->_styles['style-mobile'] = array();
				$desktop = false;
			}
			if($desktop) {
				$this->_styles['style-desctop'] = array();
			}*/

			foreach($this->_styles as $s => $sInfo) {
				if(isset($sInfo['for'])) {
					if(($sInfo['for'] == 'frontend' && is_admin()) || ($sInfo['for'] == 'admin' && !is_admin()))
						continue;
				}
				$canBeSubstituted = true;
				if(isset($sInfo['substituteFor'])) {
					switch($sInfo['substituteFor']) {
						case 'frontend':
							$canBeSubstituted = !is_admin();
							break;
						case 'admin':
							$canBeSubstituted = is_admin();
							break;
					}
				}
				if($canBeSubstituted && file_exists(EBBS_TEMPLATES_DIR. $defaultPlugTheme. DS. $s. '.css')) {
					frameEbbs::_()->addStyle($s, EBBS_TEMPLATES_PATH. $defaultPlugTheme. '/'. $s. '.css');
				} elseif($canBeSubstituted && file_exists(utilsEbbs::getCurrentWPThemeDir(). 'csp'. DS. $s. '.css')) {
					frameEbbs::_()->addStyle($s, utilsEbbs::getCurrentWPThemePath(). '/toe/'. $s. '.css');
				} elseif(!empty($sInfo['path'])) {
					frameEbbs::_()->addStyle($s, $sInfo['path']);
				} else {
					frameEbbs::_()->addStyle($s);
				}
			}
			add_action('wp_head', array($this, 'addInitJsVars'));
		}

        parent::init();
    }
	/**
	 * Some JS variables should be added after first wordpress initialization.
	 * Do it here.
	 */
	public function addInitJsVars() {
		/*frameEbbs::_()->addJSVar('adminOptions', 'EBBS_PAGES', array(
			'isCheckoutStep1' => frameEbbs::_()->getModule('pages')->isCheckoutStep1(),
			'isCart' => frameEbbs::_()->getModule('pages')->isCart(),
		));*/
	}
}
