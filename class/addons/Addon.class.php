<?php

namespace addon;

/*
[[addon=graph alias=SSEWorkloadGraph datastore=app.stats.SSEWorkloadModel:getDataStore]]
*/

uses ("core.base.Quobject");
uses ("core.base.Singleton");

abstract class Addon extends \core\base\Quobject {

    protected $name = null;
    protected $alias = null;
    private $_hooks = null;
    private $_class_prefix = null;
    protected $config;
    protected $datastore = array();
    protected $hooks;
    public $controller;
    protected $_prototype;

    public function __construct($name, $alias, $prototype = null, $datastore = null) {
        parent::__construct();
        $this->name = strtolower($name);
        $this->alias = $alias;
        $this->_prototype = $prototype;
        $this->_class_prefix = "addon.".$this->name.".class.".ucfirst($this->name);

        \core\Quantum::registry("addon_".$this->getClassName(), "active");
        
        if (is_array($datastore)) {
            throw new \core\exceptions\IllegalArgumentException("The datastore must be a string or NULL");
        } elseif (is_null($datastore)) {
            $this->datastore["name"] = $this->_class_prefix."Model";
        } else {
            $_ds = explode(":", $datastore);
            debug("Setting-up addon '$name' (Alias: $alias) width datastore '".$datastore."'", $this); #_ds[0].":".$_ds[1]."'", $this);
            $this->datastore["name"] = $_ds[0];
            $this->datastore["method"] = $_ds[1];
        }

        $this->configure();
        $this->dispatch();  # maybe obsolete?

        $this->invoke();
    }

    public function getPrototype() {
        return $this->_prototype;
    }

    public function configure() {
        debug ("Configuring Addon '".$this->name."' (".$this->alias.") with datastore '".$this->datastore["name"]."'", $this);
        debug ("Using Controller: ".$this->_class_prefix."Controller", $this);
        debug ("Using Model: ".$this->datastore["name"], $this);
        debug ("Using View: ".$this->_class_prefix."View", $this);

        uses ($this->_class_prefix."Controller", $this->datastore["name"], $this->_class_prefix."View");
        $controller_name = preg_replace("/Addon/", "Controller", $this->getClassName());

        $this->_hooks = \core\base\Singleton::getInstance('\addon\Hooks');
        $this->_hooks->setHookPoints($this->hooks);
        $this->_hooks->runHooks();

        trace ("Creating controller", $this);
        $this->controller = New $controller_name( array( "model" => $this->datastore["name"] ), false);
        $this->controller->configure($this->getPrototype());
    }

    abstract public function dispatch();

    public function invoke() {

        # FIXME. da muss man noch ran

        trace("Running addon controller", $this);
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $this->controller->onGet();
        } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->controller->onPost();
        }
    }

}

?>
