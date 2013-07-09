<?php

namespace core\plugins\calendar;

uses (
    "core.plugins.Plugin",
    "core.plugins.calendar.CalendarData",
    "core.template.rainbow.RainbowTemplate"
);

class CalendarPlugin extends \core\plugins\Plugin {

    protected $hooks        = array("run");

    protected $model        = null;

    public function run($content) {

        $template = new \core\template\rainbow\RainbowTemplate();
        $template->assign((array) $this->model->get());
        $content .= $template->draw(sprintf("%s/class/plugins/calendar/templates/calendar.xhtml", QUANTUM_ROOT), true);

        return $content;
    }

}

?>
