<?php

uses (
    "core.base.Quobject",
    "core.base.Singleton",
    "core.data.DatabaseConnectionManager",
    "core.data.activerecord.Database",
    "core.data.activerecord.Table",
    "core.data.activerecord.Column"
);

abstract class Model extends Quobject {

    private $connection_manager     = null;

    private $datasource             = null;

    protected $database             = null;
   
    protected $table                = null;

    public function __construct() {
        parent::__construct();
        $this->connection_manager = Singleton::getInstance("DatabaseConnectionManager");
    }

    public function __destruct() {
        $this->datasource->free();
        parent::__destruct();
    }

    protected function setDataSource($dsn) {
        $this->datasource = $this->connection_manager->getInstance($dsn);
        $dsn_settings = $this->connection_manager->getInstanceSettings($dsn);
        $this->database = new Database($dsn_settings["database"]);
        $this->datasource->connect();
    }

    protected function query($query, $unbuffered = false) {
        return $this->datasource->query($query, $unbuffered);
    }

    protected function next() {
        return $this->datasource->next();
    }

    protected function unbuffered_next() {
        return $this->datasource->unbuffered_next();
    }

    protected function 

    

}

?>
