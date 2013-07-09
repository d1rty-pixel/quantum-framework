<?php

namespace core\application;

uses (
    "core.base.Quobject",
    "core.security.ACL",
    "core.security.authentication.Provider"
);

class Security extends \core\base\Quobject {

    protected $use_authentication       = false;

    protected $authentication_provider  = null;

    private $provider                   = null;

    protected $supported_vars           = array("use_authentication", "authentication_provider");

    public function __construct() {
        parent::__construct();

        if ($this->use_authentication) {
            $this->provider = \core\security\authentication\Provider::getInstance();
            $this->provider->registerProvider($this->authentication_provider);
        } 

        # initialize ACL with empty data or get the one from the authentication provider
        $acl = \core\security\ACL::getInstance();
    }

}

?>
