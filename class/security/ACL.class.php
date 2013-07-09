<?php

namespace core\security;

uses (
    "core.base.AbstractSingleton",
    "core.security.acl.User",
    "core.security.acl.Roles"
);

class ACL extends \core\base\AbstractSingleton {

    private $default_role_id    = "quantum-default-user-role";

    public $user        = null;

    public $roles       = null;

    public function __construct() {
        $this->user     = new \core\security\acl\User();
        $this->roles    = new \core\security\acl\Roles();
        $this->addRole($this->default_role_id);
    }

    public function addRole($role_id, $role_name = null) {
        if (is_null($role_name))    $role_name = $role_id;
        return $this->roles->addRole($role_id, $role_name);
    }

    public function addUserRights($rights) {
        $role = $this->addRole($this->default_role_id);
        return $role->addRights($this->default_role_id, $rights);
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getRole($role_id) {
        return $this->roles->getRoleByID($role_id);
    }

    public function getRights($role = null) {

    }

    public function hasRole($role) {

    }

    public function hasRight($right) {

    }

}

?>
