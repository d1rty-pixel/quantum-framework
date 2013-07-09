<?php

namespace core\security\acl;

uses (
    "core.base.Quobject"
);

class User extends \core\base\Quobject {

    private $session_id     = null;

    private $user_id        = null;

    private $user_name      = null;

    private $first_name     = null;
   
    private $last_name      = null;

    public function setUser($user_id, $user_name, $first_name, $last_name, $session = null) {
        $this->user_id      = $user_id;
        $this->user_name    = $user_name;
        $this->first_name   = $first_name;
        $this->last_name    = $last_name;

        if (is_null($session)) {
            $this->session_id   = session_id();
        } else if ($session instanceof \core\session\Session) {
#            print_r($session);
        }
    }

    public function getUserID() {
        return $this->user_id;
    }

    public function getUserName() {
        return $this->user_name;
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

}

?>
