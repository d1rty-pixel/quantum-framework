<?php

uses ("core.base.Quobject", "core.base.Singleton");
uses ("core.template.tpl.TplTemplate");

class Maintenance extends Quobject {

	private $template		= null;

	public function __construct() {
		parent::__construct();
		$this->template = Singleton::getInstance("TplTemplate");
	}

	public function display($template) {
		trace("Entering maintenance mode.", $this);
		$this->template->addFileContent($template);
	}

}

?>
