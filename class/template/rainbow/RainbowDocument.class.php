<?php

namespace core\template\rainbow;

uses (
    "core.template.Document",
    "core.config.Config",
    "core.base.Singleton",
    "core.template.rainbow.RainbowTemplate",
    "core.plugins.xhtmlheader.XhtmlHeaderPlugin"
);

class RainbowDocument extends \core\template\Document {

    public function parseContainerFile() {
        $container = $this->determineContainerFile(
            $this->config->get("document.contentprefix"),
            $this->config->get("document.fallbackprefix")
        );

        $template = new \core\template\rainbow\RainbowTemplate();
        $data = $template->draw($container, true);
        $this->add2Document($data);
    }

}

?>
