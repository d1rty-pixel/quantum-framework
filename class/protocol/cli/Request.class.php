<?php

uses ("core.base.RequestBase");

## core.protocol.cli.Request

final class Request extends \core\base\RequestBase {

	protected function importRequestVars() {
        @$args = $_SERVER["argv"];
        for ($i=1; $i <= count($args) - 1; $i++) {
            $arg_string = $_SERVER["argv"][$i];
            $arg_string = preg_replace("/^-{1,}/", "", $arg_string);
            $arg_array = preg_split("/=/", $arg_string);
            \core\Quantum::registry("args_".$arg_array[0], $arg_array[1]);
        }
	}

	public static function isPost($arg = null) {
		return false;
	}

	public static function isGet($arg = null) {
		return true;
	}

}

?>
