<?php

namespace core\base;

uses (
    "core.base.Singleton",
    "core.exceptions.MethodNotImplementedException",
    "core.parser.JSONParser"
);


/**
 * Class Quobject
 * 
 * quantum objects - everything depends on.
 * enables detailed logging and tracking features on relational objects
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base
 */
class Quobject {

	private $q	= null; # name of object 

    protected $supported_vars = array();

    

	public function __construct() {
		$this->q = get_class($this);
		trace("Quobject '".$this->q."' created",$this);
        $this->applyClassDefaults();
        $this->applyClassConfiguration();
	}

	public function __destruct() {
		trace("Quobject '".$this->q."' destroyed",$this);
		unset($this);
	}

    public function __call($name, $args) {
        if (method_exists($this, $name)) return $this->$name($args);
        throw new \core\exceptions\MethodNotImplementedException("Method '$name' is not implemented in class '".$this->q."'");
    }

    protected function __set($key, $value) {
#        if (!in_array($key, array_keys(get_object_vars($this)))) return;

        if (!in_array($key, $this->supported_vars)) return;

        $reflector = new \ReflectionClass(get_class($this));
        $prop = $reflector->getProperty($key);
        $prop->setAccessible(true);

#        if (is_bool($prop->getValue())) {
        if (is_bool($this->$key)) {
            $prop->setValue($this, (strtolower($value) == 'true' || $value == 1) ? true : false);
        } else {
            $prop->setValue($this, $value);
        }

    }

	/**
	 * self destruction of an object
	 */
	public function self_destruct() {
        trace("Self-destruction enabled", $this);
		self::__destruct();
	}

	public function getClassName() {
        if (@is_object($this)) return $this->q;
        return "static\\undefined";
	}

    public function getQFClassName($class_name = null) {
        return str_replace("\\", ".", (!is_null($class_name)) ? $class_name : $this->q);
    }

    private function applyClassDefaults() {
        $this->loadClassConfigurationFile(sprintf("%s/config/", QUANTUM_ROOT));
    }

    protected function applyClassConfiguration() {
        $this->loadClassConfigurationFile(CONFIG_PATH);
    }

    private function loadClassConfigurationFile($prefix_path) {
        $class_config_file = sprintf("%s/%s.json", $prefix_path, $this->getQFClassName());

        if (!file_exists($class_config_file)) {
            return;
        }

        if (!method_exists($this, "__set")) {
            error(sprintf("Class %s wants to set custom configuration, but has no __set method declared. Ignoring configuration", $this->q), $this);
            return;
        }

        debug(sprintf("Reading class configuration from file %s", $class_config_file), $this);

        $content = null;
        try {
            $file = new \core\filesystem\File($class_config_file);
            $content = $file->get();
            $file->close(); 
        } catch (\core\exceptions\OperationNotPermittedException $e) {
            return;
        }

        $json = new \core\parser\JSONParser();
        try {

            $data = $json->decode($content);
        } catch (\core\exceptions\IllegalArgumentException $e) {
            return $e->getMessage();
        }

        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

}

?>
