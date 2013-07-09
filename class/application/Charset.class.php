<?php

namespace core\application;

uses ("core.base.Quobject");

class Charset extends \core\base\Quobject {

    private $charset            = "utf-8";

    protected $supported_vars   = array("charset");

    public function __construct() {
        parent::__construct();
        debug(sprintf("Charset is set to '%s'", $this->getCharset()));
    }

    public function getCharset() {
        return $this->charset;
    }

}

?>
