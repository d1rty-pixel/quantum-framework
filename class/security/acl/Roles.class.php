<?php

namespace core\security\acl;

uses (
    "core.base.Quobject",
    "core.security.acl.Role",
    "core.exceptions.NoSuchInstanceException"
);

class Roles extends \core\base\Quobject {

    private $roles  = array();

    public function addRole($role_id, $role_name) {
        $role = new \core\security\acl\Role($role_id, $role_name);
        try {
            array_push($this->roles, $role);
        } catch (\Exception $e) {
            return false;
        }
        return $role;
    }

    public function getRoleByID($role_id) {
        foreach ($this->roles as $role) {
            if ($role->getID() == $role_id)
                return $role;
        }
        return null;
    }

}

?>
