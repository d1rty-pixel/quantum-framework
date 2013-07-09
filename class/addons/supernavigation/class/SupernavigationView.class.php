<?php

uses ("core.base.mvc.View", "core.template.layout.UlliLayout");

class SupernavigationView extends View {

    public function display($conf, $struct) {
        $ulli = new UlliLayout($conf, $struct);
        $this->template->add($ulli->create());
    }
}

?>
