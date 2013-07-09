<?php

namespace core\plugins\xhtmlheader;

uses (
    "core.plugins.Plugin",
    "core.plugins.xhtmlheader.XhtmlHeaderData",
    "core.template.rainbow.RainbowTemplate"
);

class XhtmlHeaderPlugin extends \core\plugins\Plugin {

    protected $hooks        = array("run");

    protected $model        = null;

    public function run($content) {

        $template = new \core\template\rainbow\RainbowTemplate();
        $template->assign((array) $this->model->get());
        $content .= $template->draw(sprintf("%s/class/plugins/xhtmlheader/templates/head.xhtml", QUANTUM_ROOT), true);

        return $content;
    }

}

?>
