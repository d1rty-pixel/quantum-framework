<?php

namespace core\scriptlet;

uses ("core.scriptlet.ContentScriptlet");

class XhtmlScriptlet extends \core\scriptlet\ContentScriptlet {

	public function setup() {
		$this->setContentType("text/html");

		$this->determineSuitingRequestParameter($this->config->get("default.showparam"), $this->config->get("default.page"));

		$this->determineContentPrefixes(
			(isset($_SESSION["css"]))	? $_SESSION["css"]				: null,
			(!\Request::isEmpty("css"))	? \Request::getArgument("css")	: null
		);
	}

	public function dispatch() {
		$this->document->parseContainerFile();
	}

}

?>
