<?php

## obsolet!? oO

uses ("core.base.Singleton");

class SimpleXML extends Singleton {

	private static $object = NULL;

	function __construct($rootnode="root") {
		$document = "<?xml version='1.0' standalone='yes'?><".$rootnode."></".$rootnode.">";
		self::$object = new SimpleXMLElement($document);
	}

	public static function returnDocument() {
		return self::$object->asXML();
	}

	public static function addChild($name,$value="",$namespace="") {
		return self::$object->addChild($name,$value,$namespace);
	}



}

?>
