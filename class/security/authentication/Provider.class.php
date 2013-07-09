<?php

namespace core\security\authentication;

uses (
    "core.base.AbstractSingleton"
);

class Provider extends \core\base\AbstractSingleton {

    private $providers  = array();

    public function registerProvider($provider) {
        $provider_class_name = sprintf("core.security.authentication.provider.%s", $provider);
        $provider_class = sprintf('\\%s', str_replace('.', '\\', $provider_class_name));
        uses ($provider_class_name);
        $this->providers[$provider] = new $provider_class();
    }

    public function getProvider($provider) {
        return $this->providers[$provider];
    }

}

?>
