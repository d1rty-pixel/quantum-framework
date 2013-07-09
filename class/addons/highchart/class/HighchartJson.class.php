<?php

class HighchartJson extends Quobject {

    private $js     = null;

    public function __construct($js) {
        parent::__construct();
        $this->js = $js;
    }

    public function get() {
        return $this->js;
    }

}

?>
