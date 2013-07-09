<?php

uses ("core.base.Quobject", "core.exceptions.IllegalArgumentException");

class HighchartDataProperties extends Quobject {

    private $properties = array();

    public function __construct($config = array()) {
        parent::__construct();
        $this->applyConfig($config);
    }

    private function applyConfig($config) {
        $this->properties = (Object) array_merge($this->prototype(), (Array) $config);
    }

    private function prototype() {
        return array(
            "type"          => "spline",
            "visible"       => true,
            "name"          => sprintf("Unnamed Series %s", randomID()),
            "yAxis"         => 0,
        );
    }

    public function setProperty($key, $value) {
        $this->properties->$key = $value;
    }

    public function getProperties() {
        return $this->properties;
    }

    public function getProperty($name) {
        if (is_null($name)) throw new IllegalArgumentException("Property name must not be null");
        return $this->properties->$name;
    }

}

?>
