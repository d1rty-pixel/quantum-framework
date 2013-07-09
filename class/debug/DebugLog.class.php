<?php

uses ("core.debug.Logger");

/**
 * Class DebugLog
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006-2008 Tristan Cebulla
 * @package core.debug
 */
class DebugLog extends Logger {

	function __construct() {
		parent::__construct();
		$this->addMessage("Quantum DebugLog enabled.");
		$this->addMessage("Please note that this debug output contains sensitive data like database access or passwords.\nStarting DebugLog now ...\n\r");
	}

	function __destruct() {
		Quantum::registry("debug_log",$this->getMessages());
		parent::__destruct();
	}

	public function add($content,$object=NULL) {
		if (is_object($object)) $classname = get_class($object);
		else if (is_string($object)) $classname = $object;
		else $classname = "static/undefined";

		$this->addMessage("(".$classname.") ".$content);
	}

}

?>
