<?php

uses ("core.base.Quobject");

abstract class DataStore extends Quobject { 

    private $_model;
    protected $ds;
    protected $datastore;

    public function __construct() {
        parent::__construct();
        var_dump($this);
        uses ($this->model["path"]);
        $name = $this->model["classname"];
        $this->_model = new $name();

    }

    public function getDataStore() {
        return $this->datastore;
    }

    abstract public function assign($ds_key, $value);

}

?>
