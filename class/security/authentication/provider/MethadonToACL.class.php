<?php

namespace core\security\authentication\provider;

uses (
    "core.base.Quobject"
);

class MethadonToACL extends \core\base\Quobject {

    public function __construct($methadon_data, $acl) {
        parent::__construct();

        foreach ($this->parse($methadon_data) as $role => $rights) {
            $role = $acl->addRole($role);
            $role->addRights($rights);
        }

    }

    private function parse($array) {
        $roles = array();

        if (!is_array($array)) $array = explode(",", $array);

        foreach ($array as $item) {
            list($role_id, $right_name) = explode("|", $item);
            if (!isset($roles[$role_id]) || !is_array($roles[$role_id])) $roles[$role_id] = array();
            array_push($roles[$role_id], $right_name);
        }

        return $roles;

    }

}

?>
