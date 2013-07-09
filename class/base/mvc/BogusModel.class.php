<?php

namespace core\base\mvc;

/**
 * Class BogusModel
 *
 * This is part of the MVC concept design here used by the Quantum Framework.
 * The Model gets all needed data provided by database information, files and any other data source.
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base.mvc
 */
abstract class BogusModel extends \core\base\Quobject {

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
