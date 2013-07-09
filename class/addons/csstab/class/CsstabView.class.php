<?php

uses ("core.base.mvc.View");

class CsstabView extends View {

    public function display($data) {
        $this->template->addAddonTemplate("tabpane", "csstab");

        foreach ($data as $tabmenu_id => $tab_data) {
            $this->template->setVar("tabpane", "tabMenu", array(
                "TABMENU_ID"    => $tabmenu_id,
                "TABMENU_TEXT"  => $tab_data["name"],
            ));

            $this->template->setVar("tabpane", "tabPane", array(
                "TABMENU_ID"        => $tabmenu_id,
                "TABPANE_CONTENT"   => $tab_data["content"],
            ));

        }


        $this->template->parse("tabpane");
    }
}

?>
