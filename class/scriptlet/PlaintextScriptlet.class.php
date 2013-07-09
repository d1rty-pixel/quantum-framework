<?php

uses ("core.scriptlet.ContentScriptlet");

class PlaintextScriptlet extends ContentScriptlet {

	public function setup() {
		$this->setContentType("text/plain");

        $this->determineContentPrefixes(
            null,
            (!Request::isEmpty("prefix"))	? Request::getArgument("prefix")   : null
        );

		$this->determineSuitingRequestParameter("qf_controller", null);
	}

	public function dispatch() {
		$this->executeSuitingRequestParameter();

		// add a newline on the end
		$this->template->add("\n");
	}

}

?>
