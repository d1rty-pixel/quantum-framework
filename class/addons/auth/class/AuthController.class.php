<?php

uses ("core.base.mvc.Controller");
uses ("core.base.QuantumAuthentication");

class AuthController extends Controller {

    private $show       = null;
    private $action     = null;
    private $auth       = null;

    public function init() {
        $this->show     = Request::getArgument("show");
        $this->action   = Request::getArgument("action");
        $this->auth     = new QuantumAuthentication();
    }

    public function configure($data) {

    }

    public function onGet() {
        if ($this->action == "register") {
            $this->view->display_register_form();
        } else if ($this->action == "login") {
            $this->view->display_login_form();
        }
    }

    public function onPost() {
        var_dump(array("get" => $_GET, "post" => $_POST));
        if ($this->action == "checklogin") {
            
        } elseif ($this->action == "checkregister") {

            $this->action = "register";
            $this->onGet();
        }
    }

}

?>
