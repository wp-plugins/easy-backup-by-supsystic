<?php
abstract class viewEbbs extends baseObjectEbbs {
    /* 
     * @deprecated
     */
    protected $_tpl = EBBS_DEFAULT;
    /*
     * @var string name of theme to load from templates, if empty - default values will be used
     */
    protected $_theme = '';
    /*
     * @var string module code for this view
     */
    protected $_code = '';

    public function display($tpl = '') {
        $tpl = (empty($tpl)) ? $this->_tpl : $tpl;

        if(($content = $this->getContent($tpl)) !== false) {
            echo $content;
        }
    }
	public function getPath($tpl) {
		$path = '';
		$code = $this->_code;
		$parentModule = frameEbbs::_()->getModule( $this->_code );
		$plTemplate = frameEbbs::_()->getModule('options')->get('template');		// Current plugin template
		if(empty($plTemplate) || !frameEbbs::_()->getModule($plTemplate))
			$plTemplate = '';
		if(file_exists(utilsEbbs::getCurrentWPThemeDir(). 'ebbs'. DS. $code. DS. $tpl. '.php')) {
            $path = utilsEbbs::getCurrentWPThemeDir(). 'ebbs'. DS. $code. DS. $tpl. '.php';
        } elseif($plTemplate && file_exists(frameEbbs::_()->getModule($plTemplate)->getModDir(). 'templates'. DS. $code. DS. $tpl. '.php')) {
			$path = frameEbbs::_()->getModule($plTemplate)->getModDir(). 'templates'. DS. $code. DS. $tpl. '.php';
		} elseif(file_exists($parentModule->getModDir(). 'views'. DS. 'tpl'. DS. $tpl. '.php')) { //Then try to find it in module directory
            $path = $parentModule->getModDir(). DS. 'views'. DS. 'tpl'. DS. $tpl. '.php';
        }
		return $path;
	}
	public function getModule() {
		return frameEbbs::_()->getModule( $this->_code );
	}
	public function getModel() {
		return frameEbbs::_()->getModule( $this->_code )->getController()->getModel();
	}
    public function getContent($tpl = '', $data = array()) {
        $tpl = (empty($tpl)) ? $this->_tpl : $tpl;
        $path = $this->getPath($tpl);
        if($path) {
            $content = '';
            ob_start();
            extract($data);
            require($path);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
        return false;
    }
    public function setTheme($theme) {
        $this->_theme = $theme;
    }
    public function getTheme() {
        return $this->_theme;
    }
    public function setTpl($tpl) {
        $this->_tpl = $tpl;
    }
    public function getTpl() {
        return $this->_tpl;
    }
    public function init() {

    }
    public function assign($name, $value) {
        $this->$name = $value;
    }
    public function setCode($code) {
        $this->_code = $code;
    }
    public function getCode() {
        return $this->_code;
    }
	
	/**
	 * This will display form for our widgets
	 */
	public function displayWidgetForm($data = array(), $widget = array(), $formTpl = 'form') {
		$this->assign('data', $data);
        $this->assign('widget', $widget);
		if(frameEbbs::_()->isTplEditor()) {
			if($this->getPath($formTpl. '_ext')) {
				$formTpl .= '_ext';
			}
		}
		self::display($formTpl);
	}
}
