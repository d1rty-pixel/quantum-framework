<?php

namespace core\scriptlet;

uses("core.scriptlet.ProtocolScriptlet");
uses("core.protocol.http.Session");

class HttpScriptlet extends \core\scriptlet\ProtocolScriptlet {

	protected function init() {
		# session anhand der config initialisieren
		# request ist statisch, muss nix initialisiert werden
		$session = \core\protocol\http\Session::getInstance();
	}

}

?>
