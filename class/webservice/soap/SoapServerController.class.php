<?php

uses ("core.base.mvc.Controller");

class SoapServerController extends Controller {

    const
        SOAP_1_1    = "1.1",
        SOAP_1_2    = "1.2";

    private $wsdl_uri       = null;
    private $soap_version   = SOAP_1_2;
    private $charset        = "utf-8";
    private $classmap       = array();
    private $soap_uri       = null;

    private $server         = null;

    public function init() {
        if (is_null($this->soap_uri)) {
            $this->setURI(BASE_URI);
        }
    }

    public function setWSDL($wsdl) {
        $this->wsdl_uri = $wsdl;
    }

    public function setURI($uri) {
        $this->soap_uri = $uri;
    }

    public function setEncoding($charset) {
        $this->charset = $charset;
        trace("Setting encoding charset to '$charset'", $this);
    }

    public function setSoapVersion($version) {
        $map = array(
            "1.1"   => SOAP_1_1,
            "1.2"   => SOAP_1_2,
        );

        if (!isset($map[$version])) {
            throw (new IllegalArgumentException("SOAP Version '$version' not supported (1.1 or 1.2)"));
        }
        trace("Setting Soap Server version to $version", $this);
        $this->soap_version = $map[$version];
    }

    public function addClassMapping($name, $class) {
        trace("Adding mapping for '$name' to class '$class'", $this);
        uses ($class);
        $this->classmap[$name] = $class;
    }

    public function setClass($longclass) {
        uses ($longclass);
        $this->class = $this->getLastNode($longclass);
    }

    public function post_process($result) {
        return $result;
    } 

    public function onGet() {
        trace("Setting up SOAP Server", $this);
        $this->server = new SoapServer(
            $this->wsdl_uri,
            array(
                "soap_version"      => $this->soap_version,
                "encoding"          => $this->charset,
                "classmap"          => $this->classmap,
                "uri"               => (is_null($this->wsdl_uri)) ? $this->soap_uri : null,
            ));

        $this->server->setClass($this->class);
        try {
            trace("Executing SOAP Server handler", $this);
            $result = $this->server->handle();
        } catch (Exception $e) {
            error("SOAP Server handler catched Exception '".$e->getMessage()."'", $this);
            return (new SoapFault($e->getMessage()));;
        }
        return $this->post_process($result);
    }

    public function onPost() {
        $this->onGet();
    }

}

?>
