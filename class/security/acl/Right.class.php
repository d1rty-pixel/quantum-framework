<?php

namespace core\security\acl;

uses (
    "core.base.Quobject"
);

class Right extends \core\base\Quobject {

    private $name           = null;

    private $description    = null;

    public function __construct($right_name, $right_description = null) {
        parent::__construct();
        if (is_null($right_description))    $right_description = $right_name;

        $this->name         = $right_name;
        $this->description  = $right_description;
    }

    public function getRightName() {
        return $this->name;
    }

    public function getRightDescription() {
        return $this->description;
    }

}

?>
