<?php

namespace core\data\driver;

uses (
    "core.data.driver.DatabaseDriver",
    "core.exceptions.MySQLException",
    "core.exceptions.IllegalArgumentException"
);

final class MySQLDriver extends \core\data\driver\DatabaseDriver {

    public function connect() {
        $this->c_id = mysql_connect($this->settings["host"], $this->settings["username"], $this->settings["password"]);
        if (!$this->c_id) {
            throw (new \core\exceptions\DatabaseException("Could not connect to database host '".$this->settings["host"]."': ".mysql_error()));
        }

        if (!mysql_select_db($this->settings["database"])) {
            throw (new \core\exceptions\DatabaseException("Could not use database '".$this->settings["database"]."': ".mysql_error()));
        }

        trace("Connected to database '".$this->settings["database"]."' on host '".$this->settings["host"]."'", $this);
    }

    public function _query($query) {
        if (!$this->connected) $this->connect();

        debug("Executing query '$query'", $this);

        $this->free();
        if ($this->unbuffered) {
            $this->q_id = mysql_unbuffered_query($query);
        } else {
            $this->q_id = mysql_query($query);
        }

        if (!$this->q_id) {
            throw (new \core\exceptions\DatabaseException("Could not execute query '$query': ".mysql_error()));
        }

        return $this->q_id;
    }

    public function num() {
        if (!$this->connected) $this->connect();

        if ($this->q_id) $this->num_rows = mysql_num_rows($this->q_id);
        return $this->num_rows;
    }

    public function affected() {
        if ($this->q_id) return mysql_affected_rows();
        return 0;
    }

    public function fetch($query) {
        $this->query($query);
        return mysql_fetch_array($this->q_id);
    }

    public function unbuffered_next() {
        if (  (!is_resource($this->q_id)) || (FALSE === ($row = mysql_fetch_object($this->q_id))) ) {
            return false;
        }
        return $row;
    }

    public function next($reverse = false) {
        if (is_null($this->seek_id)) {
            $this->num();
            $this->seek_id = 0;
        } else if ( (($this->seek_id + 1) == $this->num_rows) or ($this->num_rows == 0) ) {
            $this->free();
            return;
        } else {
            $this->seek_id++;
        }
        if ($this->num_rows == 0) return;

        if (!mysql_data_seek($this->q_id, $this->seek_id)) {
            throw (new \core\exceptions\MySQLException("Cannot seek to row ".$this->seek_id.": ".mysql_error()));
        }

        return mysql_fetch_object($this->q_id);
    }

    public function free() {
        if (is_resource($this->q_id)) {
            mysql_free_result($this->q_id);
        }
        $this->seek_id = null;
    }

    public function load_tables() {
        $this->_query("SHOW TABLES");
        
    }

    public function load_columns_from_table($table) {
        if (is_null($table)) throw new \core\exceptions\IllegalArgumentException("table must not be null");

        $columns = array();

        $this->_query(sprintf("SHOW COLUMNS FROM %s", $table));
        while ($record = $this->next()) {
            array_push($columns, $record);
        }

        return $columns;
    }

}

?>
