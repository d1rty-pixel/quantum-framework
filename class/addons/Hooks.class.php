<?php

namespace addon;

uses ("core.base.Quobject");

class Hooks extends \core\base\Quobject {

    private $hooks = array(
        "classes"    => array(
            "addon.datepicker.",
        ),
    );

    private $sub_q = null;


    public function getHookPoints() {
        return $this->hookpoints;
    }

    public function setHookPoints($hooks) {
        if (!is_array($hooks)) return;
        debug("Setting addon hookpoints for '".implode("," ,array_keys($hooks))."'", $this);
        $this->hooks = $hooks;
    }

    public function runHooks() {
        debug("Running hooks", $this);
    }

}

?>
