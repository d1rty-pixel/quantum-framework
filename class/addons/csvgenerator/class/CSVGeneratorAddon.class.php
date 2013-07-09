<?php

uses ("addon.Addon");

class CSVGeneratorAddon extends Addon {

    private $model              = null;
    private $records_written    = 0;

    protected $_prototype = array();

    public function __construct($alias, $prototype = null, $datastore = null) {
        parent::__construct("csvgenerator", $alias, $prototype, $datastore);
    }

    public function __destruct() {
        $this->close();
        parent::__destruct();
    }

    public function dispatch() {
    }
    
    public function writeRecord($array) {
        $this->controller->model->writeRecord($array);
        $this->records_written++;
    }

    public function close() {
        debug($this->records_written." records written", $this);
        $this->controller->model->close();
    }

}

?>
