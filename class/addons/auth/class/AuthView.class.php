<?php

uses ("core.base.mvc.View");
uses ("core.template.layout.FormularLayout");

class AuthView extends View {

    public function display_register_form() {
        $layout = new FormularLayout();
        $layout->Data(array(
            "title"     => "Registrierung ALTA",
            "fields"    => array(
                "prename"   => array(
                    "description"   => "Vorname",
                ),
                "name"      => array(
                    "description"   => "Nachname",
                ),
                "username"  => array(
                    "description"   => "Benutzername",
                ),
                "password"  => array(
                    "input_type"    => "Password",
                    "description"   => "Passwort",
                ),
                "password_vrfy" => array(
                    "input_type"    => "Password",
                    "description2"  => "(Wiederholung)",
                ),
                "check_agb" => array(
                    "input_type"    => "Checkbox",
                    "description2"  => "AGB is geil, yo!",
                ),
            ),
            "action"      => "/auth/checkregister",
            "hidden"        => array(
                "css"       => Request::getArgument("css"),
            ),
        ));

        $this->template->add($layout->create());
    }

    public function display_login_form() {
        $layout = new FormularLayout();
        $layout->Data(array(
            "fields"    => array(
                "username"  => array(
                    "description"   => "Benutzername",
                ),
                "password"  => array(
                    "input_type"    => "Password",
                    "description"   => "Passwort",
                ),
            ),
            "action"      => "/login/checklogin",
            "hidden"        => array(
                "css"       => Request::getArgument("css"),
            ),
        ));

        $this->template->add($layout->create());
    }

}

?>
