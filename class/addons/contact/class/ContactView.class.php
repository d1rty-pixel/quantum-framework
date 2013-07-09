<?php

uses ("core.base.mvc.View");

class ContactView extends View {

    public function display_form() {
        $this->template->addAddonTemplate("form", "contact");
        $this->template->parse("form");       
    }

    public function display_error() {
        $this->template->addAddonTemplate("error", "contact");
        $this->template->parse("error");
    }

    public function display_success() {
        $this->template->addAddonTemplate("success", "contact");
        $this->template->parse("success");
    }

    public function generate_mailbody($subject, $data) {
        $tpl = new TplTemplate();
        $tpl->addAddonTemplate("mailbody", "contact");

        if ( (isset($data["name"])) || (isset($data["prename"])) ) {
            $tpl->setVar("mailbody", "name", $data);
        }

        if ( (isset($data["street"])) || (isset($data["streetnumber"])) || (isset($data["zipode"])) || (isset($data["town"])) ) {
            $tpl->setVar("mailbody", "address", $data);
        }

        if (isset($data["phone1"])) {
            $tpl->setVar("mailbody", "phone1", $data);
        }

        if (isset($data["mobile1"])) {
            $tpl->setVar("mailbody", "mobile1", $data);
        }

        if (isset($data["email"])) {
            $tpl->setVar("mailbody", "email", $data);
        }
  
        if (isset($data["message"])) {
            $tpl->setVar("mailbody", "message", $data);
        }
 
        $tpl->parse("mailbody");
        $tpl->replace("%%SUBJECT%%", "blafasel");
        $tpl->replace();

        return $tpl->getDocument();
    }

}

?>
