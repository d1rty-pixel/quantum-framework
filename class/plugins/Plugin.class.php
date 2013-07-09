<?php

namespace core\plugins;

uses (
    "core.base.Quobject",
    "core.base.Singleton",
    "core.config.Config",
    "core.plugins.PluginInterface",
    "core.exceptions.IllegalArgumentException",
    "core.exceptions.IllegalStateException"
);

class Plugin extends \core\base\Quobject implements \core\plugins\PluginInterface {

    protected $hooks            = array();

    protected $model            = null;

    protected $model_name       = null;

    protected $config           = null;

    protected $supported_vars   = array("model_name", "hooks");


    public function  __construct($options = array()) {
        $this->config = \core\base\Singleton::getInstance('\core\config\Config');
        $this->setOptions($options);
    }
    
    /**
     * Returns a list of hooks that are implemented by the plugin.
     * This should be an array containing:
     * - a key/value pair where key is hook name and value is implementing method,
     * - a value only when hook has same name as method.
     */
    public function declareHooks() {
        return $this->hooks;
    }


    public function setModel($model_name = null) {
        if (!is_null($model_name)) {
            $this->model_name = $model_name;
        }

        if (is_null($model_name) && is_null($this->model_name)) 
            throw new \core\exceptions\IllegalStateException(sprintf("No model defined for plugin %s", $this->getQClassName()));

        if (is_null($this->model_name)) 
            $this->model_name = $model_name;

        $model_class = str_replace(".", "\\", $model_name);
        
        try {
            uses ($model_name);
            $this->model = new $model_class();
        } catch (Exception $e) {
            throw new \core\exceptions\IllegalStateException(sprintf("Could not load model class %s for plugin %s", $model_class, $this->getQClassName()));
        }
    }

    /**
     * Sets plugin options.
     *
     * @var array
     */
    public function setOptions($options) {
        foreach ((array) $options as $key => $val) {
            $this->setOption($key, $val);
        }
        return $this;
    }

    /**
     * Sets plugin option.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException Wrong option name or value
     * @return Plugin
     */
    public function setOption($name, $value) {
        $method = 'set' . ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new \core\exceptions\IllegalArgumentException('Key "' . $name . '" is not a valid settings option' );
        }
        $this->{$method}($value);
        return $this;
    }

}

?>
