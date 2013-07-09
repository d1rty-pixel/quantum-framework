<?php

uses ("core.base.Authentication");

abstract class AuthenticationModel extends Authentication {

	public function __construct() {
		parent::__construct();
        $this->init();
	}

    abstract public function init();

}

?>
