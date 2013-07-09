<?php

uses (
    "core.base.Quobject",
    "core.exceptions.IllegalArgumentException",
    "core.data.activerecord.Field"
);

class Table extends Quobject {

    private $table  = null;

    private $datasource = null;

    public $fields  = array();


    public function __construct($table, $ds) {
        $this->table = $table;
        $this->loadFields();

        $this->datasource = 

    }

    private function loadFields() {
        $this->datasource->query(sprintf("SELECT COLUMNS FROM %s", $this->table));

        while ($record = $this->next()) {

        }
    }

}

?>
