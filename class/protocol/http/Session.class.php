<?php

namespace core\protocol\http;

uses (
    "core.base.AbstractSingleton"
);

# core.protocol.http.Session

/**
 * Class Session
 *
 * This (http) session class manages your http session.
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.protocol.http
 */
class Session extends \core\base\AbstractSingleton {

	/**
	 * class constructor
	 * start the session
	 */
	public function __construct($name = null) {
        parent::__construct();

        if (is_null($name)) {
            $session_id = session_id();
        } else {
            $session_id = session_id($name);
        }

        if ($session_id == "") {
            # nginx workaround
            ini_set('session.save_handler', 'files'); 
    		session_start();
	    	trace("session started", $this);
		    ini_set("session.gc_maxlifetime", \core\Quantum::registry("authentication.timeout") * 60);
        }
	}

	public static function set($key, $value) {
		$_SESSION[$key] = $value;
	}

	public static function get($key) {
		return $_SESSION[$key];
	}

	public static function remove($key) {
		if (array_key_exists($key, $_SESSION)) {
			$_SESSION[$key] = "";
			return true;
		}
		return false;	
	}

	public static function kill() {
		session_unset();
		session_destroy();
		$_SESSION = array();
	}

}

?>
