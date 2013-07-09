<?php

uses ("core.scriptlet.ContentScriptlet");

class SoapScriptlet extends ContentScriptlet {

    public function setup() {
        $this->setContentType("text/xml");

        // use default SOAP Server Controller if none is defined
        if (Request::isEmpty("qf_controller")) {
            Request::implant("qf_controller", "core.webservice.soap.SoapServerController");
        }

        $this->determineSuitingRequestParameter("qf_controller", null);

        // no need to determine content prefixes, soap produdes xml automatically
    }

    public function dispatch() {
        $this->executeSuitingRequestParameter();
    }

}

?>
