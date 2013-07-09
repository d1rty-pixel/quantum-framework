<?php

namespace core\template\rainbow;

uses (
    "core.base.Quobject",
    "core.exceptions.TemplateSyntaxErrorException",
    "core.exceptions.FileNotFoundException",
    "core.exceptions.OperationNotPermittedException",
    "core.template.rainbow.RainbowCompiler",
    "core.plugins.PluginContainer"
);

/**
 *  RainTPL
 *  --------
 *  Realized by Federico Ulfo & maintained by the Rain Team
 *  Distributed under GNU/LGPL 3 License
 *
 *  @version 3.0 Alpha milestone: https://github.com/rainphp/raintpl3/issues/milestones?with_issues=no
 */
class RainbowTemplate extends \core\base\Quobject {

    // variables
    public $var = array();

    protected $templateInfo = array(),
        $objectConf = array();
 #       $config = array(),

    /**
     * Plugin container
     *
     * @var \Rain\Tpl\PluginContainer
     */
    protected $plugins = null;

    private $template_ext       = "xhtml";

    // configuration
    protected static $conf = array(
        'checksum'          => array(),
        'charset'           => 'UTF-8',
        'debug'             => false,
        'tpl_dir'           => 'templates/',
        'cache_dir'         => 'cache/',        # move to RainbowTemplateCache
        'tpl_ext'           => 'html',
        'base_url'          => '',
        'php_enabled'       => false,
        'auto_escape'       => true,
        'remove_comments'   => true,
        'sandbox'           => true,
        'registered_tags'   => array(),
        'tags'              => array(
            'loop'              => array('({loop.*?})', '/{loop="(?P<variable>\${0,1}[^"]*)"(?: as (?P<key>\$.*?)(?: => (?P<value>\$.*?)){0,1}){0,1}}/'),
            'loop_close'        => array('({\/loop})', '/{\/loop}/'),
            'loop_break'        => array('({break})', '/{break}/'),
            'loop_continue'     => array('({continue})', '/{continue}/'),
            'if'                => array('({if.*?})', '/{if="([^"]*)"}/'),
            'elseif'            => array('({elseif.*?})', '/{elseif="([^"]*)"}/'),
            'else'              => array('({else})', '/{else}/'),
            'if_close'          => array('({\/if})', '/{\/if}/'),
            'noparse'           => array('({noparse})', '/{noparse}/'),
            'noparse_close'     => array('({\/noparse})', '/{\/noparse}/'),
            'ignore'            => array('({ignore}|{\*)', '/{ignore}|{\*/'),
            'ignore_close'      => array('({\/ignore}|\*})', '/{\/ignore}|\*}/'),
            'include'           => array('({include.*?})', '/{include="([^"]*)"}/'),
            'function'          => array('({function.*?})', '/{function="([a-zA-Z_][a-zA-Z_0-9\:]*)(\(.*\)){0,1}"}/'),
            'variable'          => array('({\$.*?})', '/{(\$.*?)}/'),
            'constant'          => array('({#.*?})', '/{#(.*?)#{0,1}}/'),
            'registry'          => array('({registry.*?})', '/registry="([^"]*)"}/'),
        )
    );

    private $config             = null;

    protected $compiler         = null;

    public function __construct() {
        $this->compiler = new \core\template\rainbow\RainbowCompiler(&$this);
        $this->config = \core\base\Singleton::getInstance('\core\config\Config');
        parent::__construct();
    }

    private function getTemplateCachePath($template) {

    }

    /**
     * Draw the template
     *
     * @param string $templateFilePath: name of the template file
     * @param bool $toString: if the method should return a string
     * or echo the output
     *
     * @return void, string: depending of the $toString
     */
    public function draw($templateFilePath, $toString = false) {
        extract($this->var);
        
        ob_start();
        try {
            require $this->checkTemplate($templateFilePath);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
        $html = ob_get_clean();

        // Execute plugins, before_parse
        $context = $this->getPlugins()->createContext(array(
            'code' => $html,
            'conf' => $this->config,
        ));

        $this->getPlugins()->run('afterDraw', $context);
        $html = $context->code;

        if ($toString)
            return $html;
        else
            echo $html;
    }

    /**
     * Draw a string
     *
     * @param string $string: string in RainTpl format
     * @param bool $toString: if the param
     *
     * @return void, string: depending of the $toString
     */
    public function drawString($string, $toString = false) {
        extract($this->var);
        // Merge local and static configurations
#        $this->config = $this->objectConf + static::$conf;
        ob_start();
        require $this->checkString($string);
        $html = ob_get_clean();

        // Execute plugins, before_parse
        $context = $this->getPlugins()->createContext(array(
            'code' => $html,
            'conf' => $this->config,
        ));
        $this->getPlugins()->run('afterDraw', $context);
        $html = $context->code;

        if ($toString)
            return $html;
        else
            echo $html;
    }

    /**
     * Configure the object
     *
     * @param string, array $setting: name of the setting to configure
     * or associative array type 'setting' => 'value'
     * @param mixed $value: value of the setting to configure
     * @return \Rain\Tpl $this
     */
/*
    public function objectConfigure($setting, $value = null) {
        if (is_array($setting))
            foreach ($setting as $key => $value)
                $this->objectConfigure($key, $value);
        else if (isset(static::$conf[$setting]))
            $this->objectConf[$setting] = $value;

        return $this;
    }

    /**
     * Configure the template
     *
     * @param string, array $setting: name of the setting to configure
     * or associative array type 'setting' => 'value'
     * @param mixed $value: value of the setting to configure
     */
/*
    public static function configure($setting, $value = null) {
        if (is_array($setting))
            foreach ($setting as $key => $value)
                static::configure($key, $value);
        else if (isset(static::$conf[$setting])) {
            static::$conf[$setting] = $value;

            static::$conf['checksum'][$setting] = $value; // take trace of all config
        }
    }

    /**
     * Assign variable
     * eg.     $t->assign('name','mickey');
     *
     * @param mixed $variable Name of template variable or associative array name/value
     * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
     *
     * @return \Rain\Tpl $this
     */
    public function assign($variable, $value = null) {
        if (is_array($variable))
            $this->var = $variable + $this->var;
        else
            $this->var[$variable] = $value;

        return $this;
    }

    /**
     * Allows the developer to register a tag.
     *
     * @param string $tag nombre del tag
     * @param regexp $parse regular expression to parse the tag
     * @param anonymous function $function: action to do when the tag is parsed
     */
    public static function registerTag($tag, $parse, $function) {
        $this->registered_tags[$tag] = array("parse" => $parse, "function" => $function);
    }

    /**
     * Registers a plugin globally.
     *
     * @param \Rain\Tpl\IPlugin $plugin
     * @param string $name name can be used to distinguish plugins of same class.
     */
    public function registerPlugin(\core\plugins\PluginInterface $plugin, $name = '') {
        $name = (string)$name ?: \get_class($plugin);

        $this->getPlugins()->addPlugin($name, $plugin);
    }

    /**
     * Removes registered plugin from stack.
     *
     * @param string $name
     */
    public function removePlugin($name) {
        $this->getPlugins()->removePlugin($name);
    }

    /**
     * Returns plugin container.
     *
     * @return \Rain\Tpl\PluginContainer
     */
    protected function getPlugins() {
        return $this->plugins
            ?: $this->plugins = new \core\plugins\PluginContainer();
    }

		/**
     * Check if the template exist and compile it if necessary
     *
     * @param string $template: name of the file of the template
     *
     * @throw \Rain\Tpl\NotFoundException the file doesn't exists
     * @return string: full filepath that php must use to include
     */
    protected function checkTemplate($template) {
        
        if (preg_match("/^\/.+/", $template)) {
            $templateFilepath = $template;
            $parsedTemplateFilepath = $this->compiler->cache->generateCacheFilename($template);

        } else {
            // set filename
            $templateName = basename($template);
            $templateDirectory = $this->config->get("path.templates"); # . $templateBasedir;
    
            $templateFilepath = $this->compiler->getTemplatePath($template);

            $parsedTemplateFilepath = $this->compiler->cache->generateCacheFilename($template);
        }

            // if the template doesn't exsist throw an error
            if (!file_exists($templateFilepath)) {
                throw new \core\exceptions\FileNotFoundException('Template ' . $templateName . ' not found in '.$templateFilepath.'!');
            }
    
        // Compile the template if the original has been updated
        #if ($this->config['debug'] || !file_exists($parsedTemplateFilepath) || ( filemtime($parsedTemplateFilepath) < filemtime($templateFilepath) )) {
#        if (!file_exists($parsedTemplateFilepath) || ( filemtime($parsedTemplateFilepath) < filemtime($templateFilepath) )) {
            $this->compiler->compileFile($templateName, $templateBasedir, $templateDirectory, $templateFilepath, $parsedTemplateFilepath);
#        }

        return $parsedTemplateFilepath;
    }

		/**
     * Compile a string if necessary
     *
     * @param string $string: RainTpl template string to compile
     *
     * @return string: full filepath that php must use to include
     */
    protected function checkString($string) {

        // set filename
        $templateName = $this->compiler->cache->generateCacheFilename($string);
        $parsedTemplateFilepath = $templateName;
        $templateFilepath = '';
        $templateBasedir = '';

        // Compile the template if the original has been updated
        if (!file_exists($parsedTemplateFilepath))
            $this->compiler->compileString($templateName, $templateBasedir, $templateFilepath, $parsedTemplateFilepath, $string);

        return $parsedTemplateFilepath;
    }

}

?>
