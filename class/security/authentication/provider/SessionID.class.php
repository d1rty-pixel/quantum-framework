<?php

namespace core\security\authentication\provider;

uses (
    "core.security.Authentication",
    "core.security.authentication.provider.MethadonToACL",
    "core.security.ACL"
);

class SessionID extends \core\security\Authentication {

    protected $name             = "PHPSESSID";

    protected $supported_vars   = array("name");

    public function __construct() {
        parent::__construct();
        $session = $this->getSessionClass(\Request::getArgument($this->name));
        if ($this->isAuthenticated()) {
            # automatically parses the __right parameter into roles/rights and adds these to the ACL
            new \core\security\authentication\provider\MethadonToACL($session::get("methadon_tool_rights"), $this->acl);
        } else {

        }
    }

    public function authenticate($username = null, $password = null) {
        $this->logout();
    }

    public function isAuthenticated() {
        $session = $this->getSessionClass();
        $methadon_session = $session::get("methadon_session");
        return !empty($methadon_session);
    }

    public function logout() {
        $session = $this->getSessionClass();
        $session::kill();
    }

}

?>
