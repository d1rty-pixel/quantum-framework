<?php

uses ("core.base.RequestBase", "core.filesystem.File");

/**
 * Class Request
 *
 * This (http) request class manages all $_REQUEST variables. They
 * are saved to the registry and can be injected if needed. 
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.protocol.http
 *
 * @var object $instance
 */
final class Request extends \core\base\RequestBase {

	/**
	 * importRequestVars
	 * @static
	 *
	 * import all $_REQUEST ($_GET, $_POST, $_COOKIE) and $_SESSION environment variables and STDIN
	 */
	protected function importRequestVars() {
        # read REQUEST
		foreach ($_REQUEST as $key => $val) {
            \Request::implant($key, $val);
		}

        # read SESSION
        foreach ($_SESSION as $key => $val) {
            \Request::implant($key, $val);
        }   

        # read STDIN
        $fs = new \core\filesystem\File("php://stdin");
        $raw = $fs->read();

        $req = null;
        if (stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") === 0) {
            $req = json_decode($raw);
        } else {
            $clean = $this->cleanInput($raw);
            parse_str($clean, $req);
        }

        if (is_array($req) && count($req) > 0) {
            foreach ($req as $key => $val) {
                \Request::implant($key, $val);
            }
        }
	}

	/**
	 * isPost
	 * @static
	 *
	 * @param String $arg
	 * @return boolean true if $arg is in $_POST
	 * @return boolean false if not
	 */
	public static function isPost($arg = null) {
        if (is_null($args))
            return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "POST") );

		return (array_key_exists($arg, $_POST));
	}

   /**
     * isGet
     * @static
     *
     * @param String $arg
     * @return boolean true if $arg is in $_GET
     * @return boolean false if not
     */
	public static function isGet($arg = null) {
        if (is_null($args))
            return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "GET") );

		return (array_key_exists($arg, $_GET));
	}

    public static function isPut($arg = null) {
        return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "PUT") );
    }

    public static function isHead($arg = null) {
        return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "HEAD") );
    }

    public static function isDelete($arg = null) {
        return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "DELETE") );
    }

    public static function isOptions($arg = null) {
        return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "OPTIONS") );
    }

    public static function isTrace($arg = null) {
        return ( (!is_null($_SERVER["REQUEST_METHOD"])) && ($_SERVER["REQUEST_METHOD"] == "TRACE") );
    }

    public static function getRequestMethod() {
        return $_SERVER["REQUEST_METHOD"];
    }

}

?>
