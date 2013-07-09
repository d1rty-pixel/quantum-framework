<?php

uses("core.base.Storage");

class Statistics extends Storage {

	function __construct() {
		parent::__construct();
	}

	private function getInformation() {

		$str = "PHP Configuration:\n";
		$str .= "PHP Server API: ".PHP_SAPI."\n";
		$str .= "Operating System: ".PHP_OS."\n";


		$this->php_config = $str;
	}


}

?>
