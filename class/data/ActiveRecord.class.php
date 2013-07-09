<?php

namespace core\data;

uses("core.base.Quobject", "core.data.DatabaseConnectionManager", "core.base.Singleton", "core.exceptions.DatabaseException");

class ActiveRecord extends \core\base\Quobject {

    protected $datasource       = null;
    private $connection_manager = null;
    private $database           = null;
    private $fields             = array();
    private $parser_data        = array();
    private $fields_loaded      = array();

    public function __construct() {
        parent::__construct();
        $this->connection_manager = \core\base\Singleton::getInstance('\core\data\DatabaseConnectionManager');
    }

    public function __destruct() {
        $this->datasource->free();
        parent::__destruct();
    }

    public function setDataSource($dsn) {
        $this->datasource = $this->connection_manager->getInstance($dsn);
        $dsn_settings = $this->connection_manager->getInstanceSettings($dsn);
        $this->database = $dsn_settings["database"];
        $this->datasource->connect();
    }

    # obsolete!
    public function set_getAs($as) {
#        trigger_Error("set_GetAs() is deprecated");
    }

    public function reset() {
        $this->parser_data = array();
    }

    private function _load_fields() {
        if (is_null($this->datasource)) {
            throw (new \core\exceptions\DatabaseException(sprintf("Datasource is not set for this Model %s", $this->getClassName() )));
        }

        # return immediately if we already loaded the field names of this table
        # FIXME -> irgendwas geht an diesem connection/databasedriver/activerecord konstrukt kaputt, wenn man mehrere datenbanken benutzt. ausserdem reconnected der staendig unnoetigerweise, das muss alles wohl nommal neu gemacht werden >.<
#        if (@$this->fields_loaded[$this->parser_data["table"]]) return true;

        $this->fields = array();
        $this->datasource->query("SHOW COLUMNS FROM ".$this->database.".".$this->parser_data["table"]);

        while ($record = $this->next()) {
#            print_r($record);
            array_push($this->fields, $record->Field);
        }

        # set fields_loaded of table to true
        $this->fields_loaded[$this->parser_data["table"]] = true;
    }

    private function _join($array) {
        $string = "";

        foreach ($array as $key => $value) {
            echo "key: $key -> value: $value\n";
#            if (!isset($
        }
    }

    # FIXME, not yet very good on more complex statements
    private function _where($array) {
        $string = "";
        # only 1 element, sth like "field = 'value'"

        foreach ($array as $logic => $data) {
            # no and/or/etc... just one statement!
            if ($logic == "0") {
                $string .= "(".$data.")";

            # we have field/value data, maybe we have more complex data too!
            } else {

                # go through each data
                $data_size = count($data);
                # recompute data_size for non_int keys
                foreach ($data as $key => $value) {
                    if (!is_int($key)) { $data_size--; }
                }

                $current = 0;
                $string .= " (";
                # go through the complete array and
                # 1. only handle the is_int($key) keys and seperate them with the logic operator
                # 2. handle the other keys and add them afterwars with () - step 1 will interact in between
                foreach ($data as $key => $value) {
                    # key is integer, only values to add (without last and/or)
                    if (is_int($key)) {
                        $string .= $this->_expand_column_list(array($value));
                        $current++;
                    
                        if ($data_size != $current) {
                            $string .= " ".$logic." ";
                        }
                    } else {
                        $current++;
                    }
                }
                $string .= ") ";

                foreach ($data as $key => $value) {
                    if (!is_int($key)) {
                        $string .= $logic." (".$this->_where(array($key => $data[$key])).") ";
                    }
                }
            } #/complex logic
        }
        return $string;
    }


    # expand column names with table name
    private function _expand_column_list($columns, $as_array = false) {
        $list = array();

        foreach ($columns as $name) {
            foreach ($this->fields as $field) {
                if (preg_match("/($name\ as)?$field/", $name) == 1) {
                    array_push($list, preg_replace("/^($field)/", $this->parser_data["table"].".$1", $name));
                    break;
                } else {
                    error(sprintf("could not find field %s in table %s", $field, $this->parser_data["table"]), $this);
                }
            }
        }
        if ($as_array) return $list;
        return implode(",", $list);
    }

    private function _quote_values($array) {
        $quoted = array();
        foreach ($array as $value) {
            array_push($quoted, "'$value'");
        }
        return implode(",", $quoted);
    }

    private function _parse($array) {
        $this->parser_data = array();
        foreach ($array as $func => $data) {
            $this->parser_data[$func] = $data;
        }
        $this->_load_fields();
    }

    private function _check_parser_data($fields) {
        foreach ($fields as $field) {
            if (!is_array($this->parser_data[$field])) return false;
        }
        return true;
    }

    public function query($query, $unbuffered = false) {
        return $this->datasource->query($query, $unbuffered);
    }

    public function next() {
        return $this->datasource->next();
    }

    public function unbuffered_next() {
        return $this->datasource->unbuffered_next();
    }

    public function num($array) {
        if (!$this->select($array, false)) return false;
        return $this->datasource->num();
    }

    public function select($array, $unbuffered = false) {
        $this->_parse($array);
        $this->_check_parser_data(array("table"));

        $query = "SELECT ";
        $query .= (is_array($this->parser_data["columns"])) ? $this->_expand_column_list($this->parser_data["columns"]) : "*";
        $query .= " FROM ".$this->database.".".$this->parser_data["table"];

        # add joins
        if (array_key_exists("join", $this->parser_data)) {
            $query .= (@is_array($this->parser_data["join"])) ? $this->_join($this->parser_data["join"]) : "";
        }

        # add where statement if it exists
        if (array_key_exists("where", $this->parser_data)) {
            $query .= ( (@is_array($this->parser_data["where"])) && (count($this->parser_data["where"]) > 0) ) ? " WHERE ".$this->_where($this->parser_data["where"]) : "";
        }

        # add group by statement if it exists
        if (array_key_exists("group", $this->parser_data)) {
            $query .= (@is_array($this->parser_data["group"])) ? " GROUP BY ".$this->_expand_column_list($this->parser_data["group"]) : "";
        }

        # add order by statement if it exists
        if (array_key_exists("order", $this->parser_data)) {
            $query .= (@is_array($this->parser_data["order"])) ? " ORDER BY ".$this->_expand_column_list($this->parser_data["order"]) : "";
        }

        # add limit statement if it exists
        if (array_key_exists("limit", $this->parser_data)) {
            $query .= (@is_array($this->parser_data["limit"])) ? " LIMIT ".$this->parser_data["limit"]["start"].",".$this->parser_data["limit"]["interval"] : "";
        }

        $this->datasource->query($query, $unbuffered);
    }

    public function getAll($array) {
        $this->select($array);
        $returned_data = array();
        while ($record = $this->next()) {
            array_push($returned_data, $record);
        }
        return $returned_data;
    }

    public function insert($array) {
        $this->_parse($array);
        $this->_check_parser_data(array("table", "values"));

        $query = "INSERT INTO ".$this->database.".".$this->parser_data["table"];

        # if $this->parser_data["columns"] is not an array we assume all columns will be filled
        $query .= (@is_array($this->parser_data["columns"])) ? " (".$this->_expand_column_list($this->parser_data["columns"]).")" : "";

        $query .= " VALUES (".$this->_quote_values($this->parser_data["values"]).")";
        $query .= ";";

        return $this->datasource->query($query);
    }

    public function delete($array) {
        $this->_parse($array);
        $this->_check_parser_data(array("table", "where"));

        $query = "DELETE FROM ".$this->database.".".$this->parser_data["table"];

        $query .= ( (@is_array($this->parser_data["where"])) && (count($this->parser_data["where"]) > 0) ) ? " WHERE ".$this->_where($this->parser_data["where"]) : "";
        $query .= ";";

        return $this->datasource->query($query);
    }

    public function update($array) {
        $this->_parse($array);
        $this->_check_parser_data(array("table", "where", "values"));

        $query = "UPDATE ".$this->database.".".$this->parser_data["table"];
        $query .= " SET ";

        # FIXME -> spaltennamen mit tabellennamen expanden!
        $query .= implode(",", $this->parser_data["values"]);
        $query .= " WHERE ".$this->_where($this->parser_data["where"]);
        $query .= ";";

        return $this->datasource->query($query);
    }

}

?>
