<?php

namespace core\base\;

uses("core.base.Quobject");

class Storage extends \core\base\Quobject {
	static $storage = array();

	function __construct() {
		parent::__construct();
	}

	// some magic :)
    static function __call($key, $params=NULL) {
		$return = NULL;
		if (count($params) != 0) {
			self::$storage[$key] = $params[0];
		} else {
			$return = self::$storage[$key];
		}
        return $return;
	}

	public static function getKeys() {
		return array_keys(self::$storage);
	}

	public static function getAll() {
		return self::$storage;
	}

	public static function reset() {
		self::$storage = array();
	}

}

?>
