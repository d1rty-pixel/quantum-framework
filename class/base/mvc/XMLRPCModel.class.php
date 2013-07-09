<?php

uses ("core.data.XMLRPC");

/**
 * Class Model
 *
 * This is part of the MVC concept design here used by the Quantum Framework.
 * The Model gets all needed data provided by database information, files and any other data source.
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base.mvc
 */
abstract class XMLRPCModel extends XMLRPC {

    public function __construct($param) {
        parent::__construct();
        
        $this->init();
    }

	/**
	 * do something on intialization
	 */
	abstract function init(); 
}

?>
