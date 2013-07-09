<?php

namespace core\webservice\rest;

uses ("core.base.Quobject");

class RestResponse extends \core\base\Quobject {

    public function __construct($data) {
        return $data;
    }

}

?>
