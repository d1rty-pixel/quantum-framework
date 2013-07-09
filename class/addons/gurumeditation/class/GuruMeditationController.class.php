<?php

namespace addon\gurumeditation;

uses (
    "core.base.mvc.Controller",
    "core.log.LogFacility"
);

class GuruMeditationController extends \core\base\mvc\Controller {

    private $exception  = null;
    private $logger     = null;
    private $log_msg    = null;

    public function init() {
    }

    public function configure($exception) {
        $this->exception    = $exception;
        $this->logger       = \core\base\Singleton::getInstance('\core\log\LogFacility'); 
    }

    public function onGet() {
        $this->view->displayHeader();
        $this->view->displayMessage(get_class($this->exception), $this->exception->getMessage(), $this->exception->getTraceAsString());

        if (\core\Quantum::registry("application.protocol") != "cli") {
            $this->view->displayStackTrace($this->exception->getTrace());
            $this->view->displayErrors($this->exception->getErrorsAsString());
            $this->view->dumpRegistry();
            $this->view->displayQLog($this->logger->getMessages());
        }

        $this->view->displayFooter();
     }

    public function onPost() {
        $this->onGet();
    }

}

?>
