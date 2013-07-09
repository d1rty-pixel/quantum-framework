<?php

namespace core\storages;

uses("core.base.Quobject");

class Storage extends \core\base\Quobject {

	protected $storage	= array();
	private $readonly	= false;

	public function __construct($data = null, $readonly = false) {
		parent::__construct();
		if ( (!is_null($data)) && (method_exists($this, "init")) ) {
			$this->init($data);
			trace("Storage initialization complete", $this);
		}
		if ($readonly) {
			$this->setReadOnly();
		}
	}

	public function setReadOnly() {
		trace("Setting Storage to readonly", $this);
		$this->readonly = true;
	}

	// some magic :)
    public function __call($key, $params = null) {
		$return = true;

		if (!is_null($params[0])) {
			if (!$this->readonly) {
				$this->storage[$key] = $params[0];
			} else {
				throw (new \core\exceptions\IOException("Trying to write to a readonly storage"));
			}
		} else {
			$return = $this->storage[$key];
		}

		return $return;
	}

	public function getKeys() {
		return array_keys($this->storage);
	}

	public function getStorage() {
		return $this->storage;
	}

	public function reset() {
        if ($this->readonly) {
    		throw (new \core\exceptions\IOException("Trying to reset a readonly storage"));
        }
		$this->storage = array();
	}

}

?>
