<?php

uses ("core.webservice.soap.OutputProcessor");

class SoapRequest extends Quobject {

    protected $processor;

    public function __construct() {
        parent::__construct();
        $this->processor = new OutputProcessor();
    }

}

?>
