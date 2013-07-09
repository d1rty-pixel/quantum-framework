<?php

namespace core\scriptlet;

uses ("core.scriptlet.ContentScriptlet");

class JsonScriptlet extends \core\scriptlet\ContentScriptlet {

	public function setup() {
		$this->setContentType("application/json");

		$this->determineSuitingRequestParameter("qf_controller", null);

        $this->determineContentPrefixes(
			null,
            (!\Request::isEmpty("css"))  ? \Request::getArgument("css")   : null
        );
	}

	public function dispatch() {
		$this->executeSuitingRequestParameter();
	}

}

?>
