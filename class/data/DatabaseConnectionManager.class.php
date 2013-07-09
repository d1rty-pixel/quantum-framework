<?php

namespace core\data;

uses ("core.base.Quobject", "core.exceptions.NoSuchInstanceException");

class DatabaseConnectionManager extends \core\base\Quobject {

    private $instances  = array();
    private $settings   = array();

    protected $dsn_data   = array();

    protected $supported_vars       = array("dsn_data");

    public function __construct($config) {
        parent::__construct();

        foreach ($this->dsn_data as $dsn_data) {
            $this->registerInstance($dsn_data->name, $dsn_data->dsn);
        }
    }

    private function registerInstance($dsn, $settings) {
        if (!array_key_exists($dsn, $this->instances)) {
            trace("Registered instance for DSN '$dsn'", $this);
            $this->instances[$dsn]  = null;
            $this->settings[$dsn]   = $settings;
            $this->createInstance($dsn);
        }
    }

    private function createInstance($dsn) {
        if (preg_match("/^(.+):\/\/([_a-zA-Z0-9]+):?(.*)@(.+)\/(.+)$/", $this->settings[$dsn], $matches) == 0) {
            throw (new \core\exceptions\DatabaseDriverException("Database connection settings incomplete for datasource '".$dsn."'"));
        }

        $this->settings[$dsn] = array(
            "host"      => $matches[4],
            "username"  => $matches[2],
            "password"  => $matches[3],
            "database"  => $matches[5],
        );

        $driver_name = $matches[1]."Driver";
        $ns_driver_name = sprintf('\core\data\driver\%s', $driver_name);
        uses ("core.data.driver.".$driver_name);
        $this->instances[$dsn] = new $ns_driver_name($this->settings[$dsn]);
        trace("Created instance for DSN '$dsn'", $this);
    }

    public function getInstance($dsn) {
        if (!array_key_exists($dsn, $this->instances)) {
            throw (new \core\exceptions\NoSuchInstanceException("No instance of DSN '$dsn' registered.", $this));
        }
        return $this->instances[$dsn];
    }

    public function getInstanceSettings($dsn) {
        if (!array_key_exists($dsn, $this->settings)) {
            throw (new \core\exceptions\NoSuchInstanceException("No instance of DSN '$dsn' registered.", $this));
        }
        return $this->settings[$dsn];
    }

}

?>
