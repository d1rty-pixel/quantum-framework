<?php

namespace core\base\mvc;

uses (
    "core.base.Quobject",
    "core.base.Singleton",
#    "core.template.tpl.TplTemplate",
    "core.exceptions.GenericQuantumException"
);

/**
 * Class Controller
 * 
 * This is part of the MVC concept design here used by the Quantum Framework.
 * The Controller analyses the requestes information.
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base.mvc
 */
abstract class Controller extends \core\base\Quobject {

	public $model           = null;

	public $view            = null;

    private $_prototype = array();

    public function __construct($args = null, $autorun = true) {
        parent::__construct();

        if (is_array($args) && isset($args["model"])) {
            $this->model    = $this->createMVCClass($args["model"]);
        }

        if (is_array($args) && isset($args["view"])) {
            $this->view     = $this->createMVCClass($args["view"], $this->model);
        }


        if (is_object($args) && isset($args->model)) {
            $this->model    = $this->createMVCClass($args->model);
        }

        if (is_object($args) && isset($args->view)) {
            $this->view     = $this->createMVCClass($args->view, $this->model);
        }

        if (!is_object($this->model)) {
            $this->model    = $this->createMVCClass(str_replace("Controller", "Model",  get_class($this)));
        }

        if (!is_object($this->view)) {
            $this->view     = $this->createMVCClass(str_replace("Controller", "View",   get_class($this)), $this->model);
        }

        # set the request condition (controls behaviour of onGet and onPost methods!)
		$this->setRequestCondition($this->_prototype["condition"]);
 
        trace("Initializing Controller", $this); 
        # initialize the controller
		$this->init();

        # autorun the the 'main' controller methods (onGet or OnPost)
        if ($autorun) {
            trace("Autorun active", $this);
            $this->runController();
        } else {
            trace ("Not autorunning Controller", $this);
        }
    }

    /*
     * setUpDefaultPrototype
     *
     * The default prototype is a set of configuration parameters concerning the model, view and condition parameter.
     */
    private function setUpDefaultPrototype() {
        $this->_prototype = array(
            "model"     => str_replace("Controller", "Model", $this->getQFClassName()),
            "view"      => str_replace("Controller", "View", $this->getQFClassName()),
            "condition" => \core\Quantum::registry("default.showparam"),
        );
    }


    private function createMVCClass($class = null, $args = null) {
        if (is_null($class)) {
            throw new \core\exceptions\IllegalArgumentException("class must not be null");
        }

        if (is_object($class)) {
            return $class;
        }

        $class = str_replace(".", "\\", $class);
        $uses_class = str_replace("\\", ".", $class);

        try {
            uses($uses_class);
            return new $class($args);
        } catch (\Exception $e) {
            print "exception: $e\n";
        }
    }


    protected function getLastNode($name) {
        $tmp = (explode(".", $name));
        return $tmp[count($tmp)-1];
    }

    /*
     * createModelInstance
     * 
     * create an instance of the model class
     * This instance can be a singleton instance or a new object (net yet recognized -> constructor via settings parameter)
     */
    private function createModelInstance() {
        $model = $this->_prototype["model"];
        $model = str_replace(".", "\\", $model);

        $this->model = \core\base\Singleton::getInstance($model);
        trace(sprintf("Created MVC-model instance '%s'", $model), $this);
        if (!is_object($this->model)) {
            throw new \core\exceptions\GenericQuantumException("Could not create model");
        }
    }

    /*
     * createViewInstance
     * 
     * create an instance of the view class
     */
    private function createViewInstance() {
        $view = $this->_prototype["view"];
        $view = str_replace(".", "\\", $view);

        $this->view = New $view($this->model);

        trace("Created view instance '$view'", $this);
        if (!is_object($this->view)) {
            throw new \core\exceptions\GenericQuantumException("Could not create view");
        }

        if (method_exists($this->view, "init")) {
            trace("Executing init in MVC-View '$view'", $this);
            $this->view->init();
        }
    }

    public function runController() {
        $method = sprintf("on%s", ucfirst(strtolower(\Request::getRequestMethod())));
        trace(sprintf("MVC-Controller: Processing request method %s via %s", \Request::getRequestMethod(), $method), $this);
        try {
            $this->$method();
        } catch (\core\exceptions\MethodNotImplementedException $e) {
            throw new \core\exceptions\GenericQuantumException(sprintf("Error while running %s in Controller %s", $method, $this->getClassName()));
        }
    }

	public function getModelInstance() {
		return $this->model;
	}

	public function getViewInstance() {
		return $this->view;
	}

	/**
	 * onPost
	 *
	 * do something when page is requested by POST
	 */
	abstract public function onPost();

	/**
	 * onGet
	 *
	 * do something when page is requested by GET
	 */
	abstract public function onGet();

	/**
	 * some global initialization
	 */
	abstract protected function init();

	/**
	 * setRequestCondition
	 *
	 * set the default request condition parameter
	 *
	 * @param String param
	 */
	protected function setRequestCondition($param) {
		$this->condition = $param;
        trace("Request condition set to '$param'", $this);
	}

    public function __destruct() {
        $this->model->self_destruct();
        $this->view->self_destruct();
        parent::__destruct();
    }
}
