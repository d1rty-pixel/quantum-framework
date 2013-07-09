<?php

uses("core.base.Quobject");

/**
 * Logger class
 *
 * Do NOT construct *any* parent constructor that does a debug(); command!
 * This would recurseviley re-instanciate this class because the (e.g.) quobject
 * constructor adds a debug message line.
 *
 * This class does not extend from the Quobject class.
 */
class Logger {

	private $log		= array();
	private $q			= NULL;

	function __construct() {
		$this->q = get_class($this);

		if ( (isset($this->log[$this->q])) && (strlen($this->log[$this->q]) == 0) ) {
			$this->addMessage("Quantum Framework Logging Facility created a new Logger '".$this->q."'.");
			$this->addMessage("This is part of the Quantum Framework.");
		}
	}

	function __destruct() {
		unset($this);
	}
	
	public function addMessage($content) {
        if (!isset($this->log[$this->q])) {
            $this->log[$this->q] = "";
        }
		$this->log[$this->q] .= $this->getdateRepresentation().": ".$content."\n";
	}

	public function getDateRepresentation() {
		return date("Y.m.d-G:i:s");
	}

	public function getMessages() {
		return $this->log[$this->q];
	}

	public function reset($facility=NULL) {
		if (is_null($facility)) {
			$this->log = array();
		} else {
			$this->log[$this->q] = array();
		}
	}

}

?>
