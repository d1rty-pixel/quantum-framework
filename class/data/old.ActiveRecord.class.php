<?php

uses("core.data.Database","core.base.Timing");

#uses ("core.base.XMLWrapper");
/**
 * Class ActiveRecord
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.data
 */
class ActiveRecord {
	public $datasource = NULL;
	private $datasource_name = NULL;
	private $datasource_config = NULL;
	private $integrity = false;

	private $database;# = Quantum::registry("database_name");
	private $table = "";
	private $limitFields = array();
	private $limitResults = "";
	private $orderByFields = array();
	private $groupByFields = array();
	private $whereFields = array();
	private $constraintFields = array();
	private $fieldObjects = array();
	private $sqlString = "";

	private $timing = NULL;

	/**
	 * class constructor
	 *
	 * get the unique instance of the database object, set the internal database name
	 * and load all fields from a table defined in astract init(); or via setTable(); 
	 */
	function __construct($datasource_name) {
		$this->datasource_name = $datasource_name;
		$this->datasource = Database::getInstance($this->datasource_name);
		$this->datasource_config = Database::getDataSourceConfiguration($this->datasource_name);

		$this->setDatabase($this->datasource_config['db']);
		$this->init();
		if ($this->loadFields()) $this->integrity = true;

		$this->timing = New Timing(false);
	}

	/**
	 * do something like this:
	 * $object->setTable("table_name");
	 * here
	 */
	#abstract function init();

	/**
	 * load all table fields into an ArField array
	 *
	 * @return true
	 */
	private function loadFields() {
		debug("loading fields in table '".$this->database.".".$this->table."'",$this);
		$sql = "SHOW COLUMNS FROM ".$this->database.".".$this->table.";";
        $result = $this->datasource->query($sql);

		while ($row = mysql_fetch_object($result)) {
			$field = new ArField();

			$field->setName($row->Field);
			$t = explode("(", $row->Type);
			
			if (count($t) > 1) {   
				$field->setType($t[0]);
				$field->setSize((int)$t[1]);
			} else {   
				$field->setType($row->Type);
				$field->setSize(false);
			}

			if ($row->Key == "PRI") $field->setPrimary();
			$this->fieldObjects[$field->getName()] = $field;
		}
		return true;
	}

    private function getFQDNField($field) {
        return $this->table.".".$field;
    }

	public function reset($hard=false) {
		if ($hard) {
			$this->table = "";
		}
		$this->limitFields = "*";
		$this->limitResults = "";
		$this->orderByFields = array();
		$this->groupByFields = array();
		$this->whereFields = array();
		$this->constraintFields = array();

#		self::__construct($this->datasource_name);
	}

	/**
	 *
	 */
	public function getIntegrity() {
		return $this->integrity;
	}

	/**
	 * set the database name 
	 */
	 public function setDatabase($database) {
		$this->database = $database;
	}

	/**
	 * set the table name
	 */
	public function setTable($table) {
		$this->table = $table;
	}

	/**
	 * return the database name
	 */
	public function getDatabase() {
		return $this->database;
	}
	
	/**
	 * return the table name
	 */
	public function getTable() {
		return $this->table;
	}

	public function addConstraintFields() {
		$args = func_get_args();

		foreach ($args as $field) {
				$this->constraintFields[] = $field;
		}
	}

	/**
	 * add a where statement to your query
	 *
	 *
	 * @param String $condition e.g. "type"
	 * @param String $comparator e.g. "="
	 * @param mixed[] $value e.g. "1"
	 * @param String $next defaults to "AND", can be any connector between to where clauses 
	 * @param Integer $level enables leveling
	 *
	 * leveling WHERE conditions enables nested conditions like
	 * (A = 1 OR B = 2) AND (C = 3)
	 *
	 * Code:
	 * addWhereField("A","=","1","OR",1);
	 * addWhereField("B","=","2","AND",1);
	 * addWhereField("C","=","3","something",2);
	 *
	 * $level does not need $next (so it can be empty or "something"),
	 * because there is no further condition to attach.
	 * if $level is left at default NULL no leveling will be done.
	 */
	public function addWhereField($condition,$comparator,$value,$next="AND",$quote=true,$level=NULL) {
		$this->whereFields[] = array($condition,$comparator,$value,$next,$quote);
	}

	/**
	 * add an ORDER BY field at the end of the query
	 *
	 * @param
	 * $method is set to ASC sorting mode (default)
	 */
	public function addOrderByField($field,$method="ASC") {
		$this->orderByFields[] = $this->table.".".$field." ".$method;
	}

	public function addGroupByField($field) {
		$this->groupByFields[] = $this->table.".".$field;
	}

	/**
	 * add fieldnames for a SELECT query
	 *
	 * SELECT $this->limitFields FROM ...
	 */
	public function setLimitFields() {
		if (!is_array($this->limitFields)) {
			$this->limitFields = array();
		}

		$args = func_get_args();
		foreach ($args as $field) {
			if ($this->fieldObjects[$field]) {
				array_push($this->limitFields, $this->getFQDNField($field));
			} else {
				throw (new DatabaseException("Field '".$field."' is no table attribute of table '".$this->database.".".$this->table."'."));
			}
		}
	}

	public function setCountLimitFields() {
		if (!is_array($this->limitFields)) {
			$this->limitFields = array();
		}

		$args = func_get_args();
		foreach ($args as $field) {
#			$field = ereg("^(?:distinct )?(.*)$", $field_string);
			if ($field != "*") {
				$return = $this->fieldObjects[$field];
			} else {
				$return = true;
			}

			if ($return) {
				array_push($this->limitFields,"count(".$this->getFQDNField($field).")");
			} else {
				throw (new DatabaseException("Field '".$field."' is no table attribute of table '".$this->database.".".$this->table."'."));
			}
		}
	}

	public function addLimit($length=20,$start=0) {
		$this->limitResults = $start.",".$length;
	}

	public function dump() {
		var_dump($this);
	}

	private function getOrderByFields() {
		$sql = " ORDER BY";
		$count = count($this->orderByFields);
		$i = 1;

		foreach ($this->orderByFields as $field) {
			$sql .= " ".$this->getTable().".".$field;
			if ($i != $count) $sql .= ","; 
			$i++;
		}

		return $sql;
	}

	public function getAll($getAs="array") {
		$limitFields = implode(",",$this->limitFields);

		$sql = "SELECT ".$limitFields." FROM ".$this->getDatabase().".".$this->getTable()."";

		if (count($this->whereFields) != 0) {
			$sql .= $this->buildWhereStatement();
		}

		if (count($this->groupByFields) != 0) {
            $group = array();
            foreach ($this->groupByFields as $fieldname) {
                $group[] = $fieldname;
            }
			$sql .= " GROUP BY ".implode(",", $group);
		}

		if (count($this->orderByFields) != 0) {
			$sql .= $this->getOrderByFields();
		}

		if (!empty($this->limitResults)) {
			$sql .= " LIMIT ".$this->limitResults;
		}

		$sql .= ";";
		
		debug("built sql query: ".$sql,$this);

		$this->timing->setStart();
		$result = $this->datasource->result($sql,$getAs);
		$this->timing->debugDiff();

		return $result;
	}

	public function insert() {
		$values = func_get_args();	
		$sql = "INSERT INTO ".$this->database.".".$this->table." (".implode(",",$this->limitFields).") VALUES (";

		for ($i=0; $i<count($values); $i++) {
			$value_str .= "\"".$values[$i]."\"";
			if ($i + 1 != count($values)) $value_str .= ",";
		}
		$sql .= $value_str;
		$sql .= ");";

		debug("built sql query: ".$sql,$this);

		$this->timing->setStart();
		$result = $this->datasource->query($sql);
		$this->timing->debugDiff();

		if (!$result) return false;
		return true;
	}

	public function update() {
		$values = func_get_args();
		$fields = explode(",",$this->limitFields);

		if (count($values) != count($fields)) throw (New DatabaseException("Number of values are not equal to number of fields for UPDATE query"));

		$str = "";		

		$sql = "UPDATE ".$this->database.".".$this->table." SET ";

		for ($i=0; $i<count($fields); $i++) {
			$str .=	$fields[$i]."='".$values[$i]."'";
			if (($i + 1) != count($fields)) $str .= ", ";
		}

		$sql .= $str;

		if (count($this->whereFields) != 0) {
			$sql .= $this->buildWhereStatement();
		} else {
			throw (New DatabaseException("No WHERE clause defined for UPDATE query."));
		}

		$sql .= ";";

		$this->timing->setStart();
		$result = $this->datasource->query($sql);
		$this->timing->debugDiff();

		if (!$result) return false;
		return true;
	}

	public function delete() {
		$sql = "DELETE FROM ".$this->database.".".$this->table." ";
		
                if (count($this->whereFields) != 0) {
                        $sql .= $this->buildWhereStatement();
                } else {
			throw (New DatabaseException("No WHERE Statement declared for 'DELETE FROM' query."));
		}

		$sql .= ";";

		debug("built sql query: ".$sql,$this);

		$this->timing->setStart();
		$result = $this->datasource->query($sql);
		$this->timing->debugDiff();

		if (!$result) return false;
		return true;
	}

	public function num() {
		$this->timing->setStart();
		$result = $this->datasource->num();
		$this->timing->debugDiff();
		return $result;
	}

	public function query($query,$getAs="array") {
		return $this->datasource->result($query,$getAs);
	}

	private function buildWhereStatement() {
		$statement = " WHERE ";

        # delete AND/OR from last where statement
		$this->whereFields[count($this->whereFields) - 1][3] = "";

        var_dump($this->whereFields[count($this->whereFields) - 1][3]);

        for ($i=0; $i<count($this->whereFields); $i++) {
            $statement .= $this->table.".".$this->whereFields[$i][0]." ".$this->whereFields[$i][1]." ";
            # quote? yes
            if ($this->whereFields[$i][4]) {
                $statement .= (is_null($this->whereFields[$i][2])) ? "null" : "'".$this->whereFields[$i][2]."'";
            } else {
                $statement .= (is_null($this->whereFields[$i][2])) ? "null" : $this->whereFields[$i][2];
            }
            $statement .= " ".$this->whereFields[$i][3]." ";
        }
		return $statement;
	}

}

class ArField {
	private $name;
	private $type;
	private $size;
	private $primary = false;
	private $value;

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setType($type) {
		$this->type = $type;
	}	

	public function setSize($size) {
		$this->size = $size;
	}

	public function setPrimary() {
		$this->primary = true;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

}


?>
