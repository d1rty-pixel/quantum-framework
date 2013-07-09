<?php

namespace core\template;

uses (
    "core.base.Quobject",
    "core.config.Config",
    "core.base.Singleton",
    "core.template.rainbow.RainbowTemplate"
);

abstract class Document extends \core\base\Quobject {

    protected $document                       = "";

    protected $config                         = null;

    public function __construct() {
        parent::__construct();
        $this->config = \core\base\Singleton::getInstance('\core\config\Config');
    }

    protected function determineContainerFile($default = null, $fallback = null) {

        $path = sprintf("%s/%s.struct.%s", 
            $this->config->get("path.templates"),
            $default,
            \core\Quantum::registry("application.output")
        );
        if (file_exists($path)) {
            return sprintf("%s.struct", $default);
        }

        $path = sprintf("%s/%s.struct.%s", 
            $this->config->get("path.templates"),
            $fallback,
            \core\Quantum::registry("application.output")
        );
        if (file_exists($path)) {
            return sprintf("%s.struct", $fallback);
        }
        throw new \core\exceptions\FileNotFoundException("Could not find container file in '$path' (fallback)");
    }

    abstract public function parseContainerFile();

    public function getDocument() {
        return $this->document;
    }    

    public function add2Document($string) {
        $this->document .= $string;
    }

    public function resetDocument() {
        $this->document = "";
    }

}

?>
