<?php

namespace core\parser;

uses(
    "core.base.Quobject"
);

abstract class Parser extends \core\base\Quobject {

    abstract public function decode($data);

    abstract public function encode($data);

}

?>
