<?php

uses("core.exceptions.PostgreSQLException","core.data.Database");

/**
 * Class PostgreSQL
 *
 * This is the PostgreSQL API class.
 *
 * @var object $cid
 * @var object $qid
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.data
 */
final class PostgreSQL extends Database { 
	private $cid;
	private $qid;

	/**
	 * class constructor
	 */
	function __construct() {
		$this->connect();
	}

	/**
	 * mysql connect
	 */
	protected function connect() {
		$connection_str = "host=".parent::getDatabaseHostname()." dbname=".parent::getDatabaseName()." user=".parent::getDatabaseUserName()." password=".parent::getDatabasePassword();

		$this->cid = @pg_connect($connection_str);
		if (!$this->cid) {
			throw (new PostgreSQLException("Could not connect to database '".parent::getDatabaseHostname()." (".mysql_error().")."));
		}

		debug("connected to database '".parent::getDatabaseName()."'",$this);
   	}
	
	public function get($query, $method="id") {
        return $this->result($query, $method);
    }

	/**
	 * mysql query
	 */
   	public function query($query) {
		Quantum::registry("query",$query);
		$this->qid = @pg_query($query);
		return $this->qid;
   	}
    	
	/**
	 * mysql_num_rows
	 */
   	public function num() {
		return pg_num_rows($this->qid);
	}    

	/**
	 * return resultset of a query
	 */
	public function result($query, $method="array") {
		$result = array();
		$this->query($query);

		while ($tmp = $this->fetch($this->qid, $method)) {
			array_push($result,$tmp);
		}

		return $result;
		$result = NULL;
	}

   /**
     * fetch
     *
     * some mysql_fetch_*-voodoo
     *
     * @param mixed[] $result result-set of a query
     * @param String $method
     * @return mixed[]
     * @exception IllegalArgumentException
     *
     * methods:
     * - id -> fetch row
     * - name -> fetch assoc
     * - array -> fetch array
     * - obj -> fetch object
     */
	public function fetch($result,$method="array") {
		switch($method) {
			case "id":	$resarray = pg_fetch_row($result);		break;
			case "name":	$resarray = pg_fetch_assoc($result);		break;
			case "array":	$resarray = pg_fetch_array($result);		break;
			case "obj":	$resarray = pg_fetch_object($result);	break;
			default:	throw (new IllegalArgumentException("Illegal fetch method '".$method."')"));
	       	}
		return $resarray;
	}

}

?>
