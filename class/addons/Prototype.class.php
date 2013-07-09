<?php

uses ("core.base.Quobject");

abstract class Prototype extends Quobject {

    private $prototype;

    abstract public function init($prototype);

    public function getPrototype() {
        return $this->prototype;
    }

}

?>
