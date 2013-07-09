<?php

uses("core.debug.Logger","core.config.Config","core.debug.LogFile");

class ApplicationLog extends Logger {

	private $user			= NULL;
	private $logfile		= NULL;

	function __construct() {
		parent::__construct();
		$this->addMessage("Quantum ApplicationLog enabled.");

#		$this->reset("ApplicationLog");
		$this->user = @$_SESSION['auth_user_firstname']." ".@$_SESSION['auth_user_name']." (".$_SESSION['auth_person_id'].")";
	}

	public function add($content,$extra=NULL) {
		$msg = $this->user." - ".$content;

		$this->addMessage($msg);

		$config = Singleton::getInstance("Config");
		$logfile = new LogFile("ApplicationLog",$config->get("applicationlog_file"));
		$logfile->write($this->getDateRepresentation().": ".$msg);
	}
	
    function __destruct() {
        Quantum::registry("application_log",$this->getMessages());
        parent::__destruct();
    }

}

?>
