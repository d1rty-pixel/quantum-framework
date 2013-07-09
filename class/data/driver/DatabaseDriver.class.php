<?php

namespace core\data\driver;

uses (
    "core.base.Quobject",
    "core.base.Timing",
    "core.exceptions.DatabaseDriverException"
);

abstract class DatabaseDriver extends \core\base\Quobject {

    protected $settings             = array();
    protected $timing               = null;
    protected $c_id                 = null;
    protected $q_id                 = null;
    protected $seek_id              = null;
    protected $num_rows             = 0;
    protected $unbuffered           = false;
    protected $connected            = false;


    public function __construct(&$settings) {
        parent::__construct();
        $this->settings = $settings;
        $this->timing = new \core\base\Timing(false);
    }

    public function query($query, $unbuffered = false) {
        if (!$this->connected) $this->connect();
        $this->unbuffered = $unbuffered;

        $this->timing->start();
        trace("Executing Query: $query", $this);
        $result = $this->_query($query);
        trace("Operation took ".$this->timing->elapsed()." seconds", $this);
        return $result;
    }

    abstract public function load_tables();

    abstract public function load_columns_from_table($table);

	abstract public function connect();

	abstract public function _query($query);

	abstract public function num();

    abstract public function affected();

    abstract public function next($reverse = false);

    abstract public function free();

}

?>
