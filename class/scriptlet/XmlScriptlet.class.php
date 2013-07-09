<?php

uses ("core.scriptlet.ContentScriptlet");

class XmlScriptlet extends ContentScriptlet {

	public function setup() {
		$this->setContentType("text/xml");

		$this->determineSuitingRequestParameter("qf_controller", null);

        $this->determineContentPrefixes(
			null,
            (!Request::isEmpty("css"))  ? Request::getArgument("css")   : null
        );
	}

	public function dispatch() {
		$this->executeSuitingRequestParameter();
	}

}

?>
