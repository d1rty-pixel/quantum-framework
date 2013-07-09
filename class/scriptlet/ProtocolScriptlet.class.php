<?php

namespace core\scriptlet;

uses ("core.base.Quobject");

abstract class ProtocolScriptlet extends \core\base\Quobject {

    # content scriptlet object (child of ContentScriptlet)
	public $content_scriptlet = null;

    /*
     * __construct
     * Create a singleton instance of the content scriptlet and return this instance
     */
	public function __construct($content_scriptlet = null) {
		parent::__construct();
		if (is_null($content_scriptlet)) {
			throw new \core\exceptions\IllegalArgumentException("Content type scriptlet is undefined");
		}

		$this->content_scriptlet = \core\base\Singleton::getInstance($content_scriptlet);
		$this->init();

		return $this->content_scriptlet;
	}

    /*
     * protected init
     * 
     */
	abstract protected function init();

    public function setup() {
        $this->content_scriptlet->setup();
    }

    public function dispatch() {
#		if (!$this->content_scriptlet->dispatch_maintenance()) {
	        $this->content_scriptlet->dispatch();
#		}
    }

    public function output() {
        $this->content_scriptlet->output();
    }

    public function getContentScriptlet() {
        return $this->content_scriptlet;
    }

}

?>
