<?php
abstract class controllerEbbs {
	protected $_models = array();
	protected $_views = array();
	protected $_task = '';
	protected $_defaultView = '';
	protected $_code = '';
	public function __construct($code) {
		$this->setCode($code);
		$this->_defaultView = $this->getCode();
	}
	public function init() {
		/*load model and other preload data goes here*/
	}
	protected function _onBeforeInit() {

	}
	protected function _onAfterInit() {

	}
	public function setCode($code) {
		$this->_code = $code;
	}
	public function getCode() {
		return $this->_code;
	}
	public function exec($task = '') {
		if(method_exists($this, $task)) {
			$this->_task = $task;   //For multicontrollers module version - who know, maybe that's will be?))
			return $this->$task();
		}
		return null;
	}

    /**
     * @param string $name
     * @return viewEbbs mixed
     */
    public function getView($name = '') {
		if(empty($name)) $name = $this->getCode();
		if(!isset($this->_views[$name])) {
			$this->_views[$name] = $this->_createView($name);
		}
		return $this->_views[$name];
	}
	public function getModel($name = '') {
		if(!$name)
			$name = $this->_code;
		if(!isset($this->_models[$name])) {
			$this->_models[$name] = $this->_createModel($name);
		}
		return $this->_models[$name];
	}
	protected function _createModel($name = '') {
		if(empty($name)) $name = $this->getCode();
		$parentModule = frameEbbs::_()->getModule( $this->getCode() );
		$className = '';
        if(is_a($parentModule, 'moduleEbbs')) {
            if (import($parentModule->getModDir() . 'models' . DS . $name . '.php')) {
                $className = toeGetClassNameEbbs($name . 'Model');
            }

            if ($className) {
                $model = new $className();
                $model->setCode($this->getCode());
                return $model;
            }
        }
        return NULL;
	}
	protected function _createView($name = '') {
		if(empty($name)) $name = $this->getCode();
		$parentModule = frameEbbs::_()->getModule( $this->getCode() );
		$className = '';
		
		if(import($parentModule->getModDir(). 'views'. DS. $name. '.php')) {
			$className = toeGetClassNameEbbs($name. 'View');
		}
		
		if($className) {
			$view = new $className();
			$view->setCode( $this->getCode() );
			return $view;
		}
		return NULL;
	}
	public function display($viewName = '') {
		$view = NULL;
		if(($view = $this->getView($viewName)) === NULL) {
			$view = $this->getView();   //Get default view
		}
		if($view) {
			$view->display();
		}
	}
	public function __call($name, $arguments) {
		$model = $this->getModel();
		if(method_exists($model, $name))
			return !empty($arguments[0]) ? $model->$name($arguments[0]) : $model->$name();
		else
			return false;
	}
	/**
	 * Retrive permissions for controller methods if exist.
	 * If need - should be redefined in each controller where it required.
	 * @return array with permissions
	 * @example :
	 return array(
			S_METHODS => array(
				'save' => array(EBBS_ADMIN),
				'remove' => array(EBBS_ADMIN),
				'restore' => EBBS_ADMIN,
			),
			S_USERLEVELS => array(
				S_ADMIN => array('save', 'remove', 'restore')
			),
		);
	 * Can be used on of sub-array - EBBS_METHODS or EBBS_USERLEVELS
	 */
	public function getPermissions() {
		return array();
	}
}
