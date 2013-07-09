<?php

namespace core\security\acl;

uses (
    "core.base.Quobject",
    "core.security.acl.Right"
);

class Role extends \core\base\Quobject {

    private $role_id    = null;
    
    private $role_name  = null;

    private $rights     = array();

    public function __construct($role_id, $role_name) {
        parent::__construct();
        $this->role_id      = $role_id;
        $this->role_name    = $role_name;
    }

    public function getID() {
        return $this->role_id;
    }

    public function reset() {
        $this->rights   = array();
    }

    public function setRights() {
        $this->reset();
        $this->addRights(func_get_args());
    }    

    public function addRights() {
        foreach (func_get_args() as $id => $args) {

            if (is_array($args)) {
                foreach ($args as $id => $right) {
                    $this->addRights($right);
                }
            } else {
                if ($args instanceof \core\security\acl\Right) {
    
                } else {
                    array_push($this->rights, new \core\security\acl\Right($args));
                }
            }
        }
    }

    public function getRights() {
        return $this->rights;
    }

    public function getRightNames() {
        $rights = array();
        foreach ($this->rights as $right) {
            array_push($rights, $right->getRightName());
        }
        return $rights;
    }

    public function hasRight($right_name) {
        foreach ($this->rights as $right) {
            if ($right->getRightName() == $right_name)   return true;
        }
        return false;
    }

}

?>
