<?php

namespace core\security;

uses (
    "core.base.Quobject",
    "core.security.ACL"
);

abstract class Authentication extends \core\base\Quobject {

    protected $acl          = null;

    public function __construct() {
        parent::__construct();
        $this->acl = \core\security\ACL::getInstance();
    }

    protected function getSessionClass($session_args = null) {
        $proto = \core\Quantum::registry("application.protocol");
        $session_class_name = sprintf("core.protocol.%s.Session", \core\Quantum::registry('application.protocol'));
        uses ($session_class_name);
        
        $session_class = sprintf('\\%s', str_replace('.', '\\', $session_class_name));
        # create a new session
        return $session_class::getInstance($session_args);
    }

    abstract public function isAuthenticated();

    abstract public function authenticate($username = null, $password = null);

    abstract public function logout();

}

?>
