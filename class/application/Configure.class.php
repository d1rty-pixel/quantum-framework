<?php

namespace core\application;

uses (
    "core.base.Quobject",
    "core.application.Protocol",
    "core.application.Charset",
    "core.application.ContentType",
    "core.application.Router",
    "core.application.Security",
    "core.config.Config"
);

final class Configure extends \core\base\Quobject {

    public $protocol;
    public $content_type;
    public $charset;
    public $router;
    public $security        = null;

    protected $application_name;

    private $config;

    public function __construct() {
        parent::__construct();

        $this->protocol = new \core\application\Protocol();
        $this->content_type = new \core\application\ContentType();
        $this->charset      = new \core\application\Charset();

        \core\Quantum::registry("application.protocol",     $this->protocol->getProtocol());
        \core\Quantum::registry("application.content_type", $this->content_type->getContentTypeScriptlet());
        \core\Quantum::registry("application.charset",      $this->charset->getCharset());

        $this->loadRequestClass();

        $config = \core\base\Singleton::getInstance('\core\config\Config');
        $cfg = $this->returnApplicationConfiguration();
        $config->import($cfg);

        \core\Quantum::registry("application.output", $this->content_type->getContentTypeScriptlet());

        # eher verschieben in xhtml scriptlet oder so  FIXME
        \core\Quantum::registry("application.css", $config->get("application.css"));
        # eher verschieben in xhtml scriptlet oder so  FIXME

        \core\Quantum::registry("path.templates", $config->get("path.templates"));
        \core\Quantum::registry("path.classes", $config->get("path.classes"));
        \core\Quantum::registry("path.modules", $config->get("path.modules"));
#        \core\Quantum::registry("path.templates", $config->get("path.templates"));
#        \core\Quantum::registry("path.templates", $config->get("path.templates"));
        \core\Quantum::registry("application.output_display", "true");

        $this->router = new \core\application\Router($config);

        $this->security = new \core\application\Security();

    }

   /*
    * loadRequestClass
    * Loads the Request class depending on the project protocol scriptlet if it is not loaded already.
    *
    * A protocol name can be given as parameter optionally.
    * @param String $protocol 
    * @access private
    * @return void
    */
    private function loadRequestClass() {
        $protocol = $this->protocol->getProtocol();
        trace(sprintf("Loading Request class of protocol type '%s'", $protocol), $this);
        uses ("core.protocol.".$protocol.".Request");
        $_request = \core\base\Singleton::getInstance("Request");
    }

    /**
     * includeApplicationConfigurationFiles
     *
     * Include *all* configuration files in CONFIG_PATH (as defined in your project-related index.php) that matches
     * pattern /.config.php$/. Only $config[] is accepted here for configuring the application!
     *
     */
    private function returnApplicationConfiguration() {
        trace(sprintf("Including application configuration files from '%s'", CONFIG_PATH), $this);
        $config = array();

        if ($handle = @opendir(CONFIG_PATH)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match("/.config.php$/",$file)) {
                    $cfg = sprintf("%s/%s", CONFIG_PATH, $file);
                    include $cfg;
                    trace(sprintf("Included file '%s'", $cfg), $this);
                }
            }
        }

        return $config;
    }

    public function post() {

    }

}

?>    
