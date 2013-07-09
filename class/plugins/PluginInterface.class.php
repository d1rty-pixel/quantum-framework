<?php

namespace core\plugins;

interface PluginInterface {

    /**
     * Returns a list of hooks that are implemented by the plugin.
     * This should be an array containing:
     * - a key/value pair where key is hook name and value is implementing method,
     * - a value only when hook has same name as method.
     */
    public function declareHooks();

    /**
     * Sets plugin options.
     *
     * @var array
     */
    public function setOptions($options);


}

?>
