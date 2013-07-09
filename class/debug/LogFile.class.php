<?php

uses("core.filesystem.FileSystem");

class LogFile extends Quobject {

	private $fs				= NULL;
	private $file			= NULL;
	private $facility		= NULL;

	function __construct($facility, $file) {
		parent::__construct();
		$this->facility = $facility;
		$this->setFile($file);
		$this->fs = New FileSystem($this->getFile());
	}

	function setFile($file) {
		$this->file = $file;
	}

	function getFile() {
		return $this->file;
	}

	function write($content=NULL) {
		if (is_null($content)) {
			$log_facility = Singleton::getInstance($this->facility);
			$log_facility->add("writing log to file ".$this->getFile().". No more output will follow, so we pseudo-die here with exitcode 0.\nDIE(0)");
			$this->fs->write($log_facility->getMessages($this->facility));
		} else {
			$this->fs->write($content."\n");
		}
	}

}

?>
