<?php

namespace core\template;

uses (
    "core.base.Quobject",
    "core.base.Singleton",
    "core.config.Config",
    "core.rtpl.RainbowTemplateCache"
);

abstract class Template extends \core\base\Quobject {

    public $cache            = null;

    protected $config           = null;

    protected $document         = null;

    private $default_contentprefix  = null;
    private $fallback_contentprefix  = null;

    public function __construct() {
        parent::__construct();
        $this->config   = \core\base\Singleton::getInstance('\core\config\Config');

        $this->default_contentprefix    = $this->config->get("document.contentprefix");
        $this->fallback_contentprefix   = $this->config->get("document.fallbackprefix");
    }

    public function getModulePath($name) {
        trace("Trying to determine module path for module '$name')", $this);
        $path = getFQPath($name, "module");

        if (!file_exists($path)) {
            throw new \core\exceptions\FileNotFoundException("Module file for '$name' was not found");
        }

        return $path;
    }

    public function getTemplatePath($name) {
        trace("Trying to determine template path for template '$name'. Current content_prefix (CSS) is '".$this->default_contentprefix."'", $this);
        $fq_template_path = getFQPath($name, "template", $this->default_contentprefix);

        # file does not exists and is not the default (in this case we can give up instantly)
        if ( (!file_exists($fq_template_path)) && ($this->default_contentprefix != $this->fallback_contentprefix) ) {
            trace("Template for default content_prefix (CSS) was not found, trying fallback", $this);
            $fq_template_path = getFQPath($name, "template", $this->fallback_content_prefix);
            # try again to find the file and throw exception when not found
            if (!file_exists($fq_template_path)) {
                throw new \core\exceptions\FileNotFoundException("Template file for fallback content_prefix (CSS) '$name' was not found");
            }
        }

        return $fq_template_path;
    }

    protected function MainContentIs() {
#        if (file_exists(
    }

    protected function processMainContent() {
        

    }


}

?>
