<?php

/**
 * Class Quantum
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core
 *
 */

namespace core;

require QUANTUM_ROOT."/functions/uses.function.php";
require QUANTUM_ROOT."/functions/datetime.function.php";
require QUANTUM_ROOT."/functions/getPath.function.php";
require QUANTUM_ROOT."/functions/logger.function.php";
require QUANTUM_ROOT."/functions/iround.function.php";
require QUANTUM_ROOT."/functions/randomgenerator.function.php";
require QUANTUM_ROOT."/functions/extendedjson.function.php";

uses (
    "core.base.Quobject",
    "core.base.Singleton",
    "core.application.Configure",
    "core.exceptions.IllegalArgumentException",
    "core.scriptlet.Scriptlet"
);

class Quantum extends \core\base\Quobject {

	private $scriptlet  = null;

	private $_configure	= null;

	private $timing     = null;

    private $version    = "0.8a";

	function __construct() {
		parent::__construct();
		trace("Quantum Framework version ".$this->version." starting up", $this);
        \core\base\Singleton::getInstance('\core\log\LogFacility');
	}
	
	function __destruct() {
		trace("Quantum Framework run completed", $this);
		parent::__destruct();
	}

	/**
	 * static registry
	 * 
	 * @access public
	 * @param mixed[] func_num_args(i);
	 * @returns array
	 * @returns String
	 */
	static final function &registry() {
		static $registry      = array();
		static $locks         = array();
		static $nullref       = NULL;

		switch (func_num_args()) {
			case 0:
				return $registry;
				break;
			case 1:
				$key = func_get_arg(0);
				return $registry[$key];
				break;
			case 2:
				$key = func_get_arg(0);
				$value = func_get_arg(1);

				if (!in_array($key, $locks)) {
					$registry[$key] = $value;
				} else {
					throw new \core\exceptions\GenericQuantumException("Can't overwrite locked registry item '".$key."'");
				}
				break;
			case 3:
				$key = func_get_arg(0);
				$value = func_get_arg(1);
				\core\Quantum::registry($key, $value);
				array_push($locks, $key);
				break;
			default: throw new \core\exceptions\IllegalArgumentException("Invalid use of quantum registry"); break;
		}
		return $nullref;
	}

	/**
	 * return Quantum instance
	 *
	 * @returns object
	 */
	public static function getInstance() {
		return $this;
	}

	/**
	 * a shell-a-like "make" command
	 * this function provides the three magic steps of each quantum project, "configure", "dispatch" and "output".
	 *
	 * this is split up to divide the processes of:
	 * - configuring the framework and application (configure),
	 * - collecting all application data and information (dispatch) and
	 * - print the whole page to the screen, depending on which protocol/scriptlet is used (output).
	 *
	 * @param String $command
	 * @exception IllegalArgumentException
	 * @exception IllegalArgumentException
	 */
	public function make($command) {
		switch($command) {
			case "configure":
				trace("make configure",$this);
				$this->_configure = new \core\application\Configure();
                uses ("core.scriptlet.Scriptlet");
                $scriptlet = \core\base\Singleton::getInstance('\core\scriptlet\Scriptlet');

                $this->scriptlet = $scriptlet->factory(\core\Quantum::registry("application.protocol"), \core\Quantum::registry("application.content_type"));
				$this->scriptlet->setup();
				break;
			case "dispatch":
				trace("make dispatch",$this);
				$this->scriptlet->dispatch();
				break;
			case "output":
				trace("make output",$this);
				$this->scriptlet->output();
				$this->make("post");
				break;
			case "post":
				trace("make post",$this);
				$this->_configure->post();
				trace("make command completed.",$this);
				$this->make("debug");
                if (\Request::getArgument("qf_debug")) {
                    $logger = \core\base\Singleton::getInstance('\core\log\LogFacility');
                    echo $logger->getMessages();
                }
				break;
			case "debug":
                $logger = \core\base\Singleton::getInstance('\core\log\LogFacility');
                $logger->writeLog();
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException(sprintf("Unknown make command '%s'", $command));
				break;
		}
	}

}

?>
