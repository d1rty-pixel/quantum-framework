<?php

uses ("core.base.mvc.Controller");

class ContactController extends Controller {

    private $show               = null;
    private $action             = null;

    public function init() {
        $this->show     = Request::getArgument("show");
        $this->action   = Request::getArgument("action");
    }

    public function configure() {
    }

    public function onGet() {
        $this->view->display_form();
    }

    public function onPost() {
        if ($this->action == "send") {
            $fifth = (ini_get("safe_mode") == "0") ? "" : "-f".$this->model->getRecipient();
            if (@mail(  $this->model->getRecipient(),
                        $this->model->getSubject(),
                        $this->view->generate_mailbody($this->model->getSubject(), $_POST),
                        $this->model->getHeader(),
                        $fifth) === true) {
                $this->view->display_success();
            } else {
                $this->view->display_error();
            }
        }
        $this->onGet();
    }

}

?>
