<?php

uses ("core.base.mvc.View");

class GraphView extends View {

    public function display($template, $filename) {
        $this->template->addAddonTemplate($template, "graph");
        $this->template->replace("%%GRAPH_IMAGE%%", $filename);
        $this->template->replace();
    }

}

?>
