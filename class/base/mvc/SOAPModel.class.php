<?php

abstract class SOAPModel extends Quobject {

    private $soapclient = null;

    public function __construct() {
        parent::__construct();
        $this->init();

##        $this->soapclient = new
    }

    abstract public function init();

}

?>
