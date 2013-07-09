<?php

abstract class XmlModel extends Quobject {

	/**
	 * execute the parent constructor
	 */
	public function __construct($param = null) {
		parent::__construct();
        $this->init();
	}

	/**
	 * do something on intialization
	 */
	abstract function init(); 

}

?>
