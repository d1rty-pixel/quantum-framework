<?php

namespace core\scriptlet;


uses ("core.template.rainbow.RainbowDocument");
uses ("core.config.Config");
uses ("core.base.Singleton");
uses ("core.exceptions.FileNotFoundException", "core.exceptions.IllegalArgumentException");
uses ("core.filesystem.File");
uses ("core.storages.ProjectXMLClassConfigurationStorage");


abstract class ContentScriptlet extends \core\base\Quobject {

	protected $default_contentprefix			= null; # will be used as css in XhtmlScriptlet
	protected $fallback_contentprefix			= null; # will be the fallback prefix if the userprefix is not available

    protected $document                 = null;

	protected $config					= null;
    protected $fs						= null;
	protected $default_parameter		= null;

	public function __construct() {
        parent::__construct();
		$this->config	= \core\base\Singleton::getInstance('\core\config\Config');

        $this->document = \core\base\Singleton::getInstance('\core\template\rainbow\RainbowDocument');
        $this->fs       = new \core\filesystem\File();

		$this->determineSuitingRequestParameter();
	}

	abstract public function setup();

	abstract public function dispatch();

    ## FIXME
	public function dispatch_maintenance() {
/*
		trace("Dispatching maintenance", $this);
        $class_config_storage = Singleton::getInstance("ProjectXMLClassConfigurationStorage");

        if ($class_config_storage->maintenance_active()) {
	        uses ("core.base.Maintenance");
            $maintenance = new Maintenance();
            $maintenance->display($class_config_storage->maintenance_template());
			return true;
        }
		return false;
*/
	}

	public function output() {
		// this is not really boolean, but meant as such
		if (\core\Quantum::registry("application.output_display") == "true") {
			echo $this->document->getDocument();
		} else {
			trace("Not displaying output as defined in project configuration", $this);
		}
	}

	protected function determineContentPrefixes($session_default = null, $request_default = null) {

		// this is default for all content scriptlets
        $this->fallback_contentprefix = $this->config->get("default.css");
		$default_prefix = $this->fallback_contentprefix;

		// all other content prefixes are unset, return and try our best
		if ( (is_null($session_default)) && (is_null($request_default)) ) {
			$this->default_contentprefix = $this->fallback_contentprefix;
		//assignn other content prefixes
		} else {
			// the session content prefix is set (usually $_SESSION["css"])
			if (!is_null($session_default)) {
				$this->default_contentprefix = $session_default;
			}

			// the request content prefix is set (usually Request::getArgument("css"))
			if (!is_null($request_default)) {
				$this->default_contentprefix = $request_default;
				// if we have a session default use that as fallback (rate session prefixes higher than the configuration prefix)
				if (!is_null($session_default)) {
					$this->fallback_contentprefix = $session_default;
				}
			}
		}

        trace("Populating ContentScriptlet prefixes for content => '".$this->default_contentprefix."' and fallback => '".$this->fallback_contentprefix."'", $this);

        $this->config->import(array(
            "document.contentprefix"    => $this->default_contentprefix,
            "document.fallbackprefix"   => $this->fallback_contentprefix,
        ));
	}

	protected function setContentType($type) {
		trace("Setting Content-Type to '$type'", $this);
		// send the content type header
		header("Content-Type: ".$type);
	}

	protected function determineSuitingRequestParameter($parameter = null, $value = null) {
		// no parameter given
		if (is_null($parameter)) {

			if (!\Request::isEmpty("qf_controller")) { 		// use qf_controller
				$this->default_parameter = "qf_controller";
			} else if (!\Request::isEmpty("qf_module")) {	// use qf_module
				$this->default_parameter = "qf_module";
			} else {										// use the configuration default
				$this->default_parameter = $this->config->get("default.showparam");
			}
			trace("Detected best suiting request parameter '".$this->default_parameter."'", $this);

		// a parameter is given
		} else if ( (!is_null($parameter)) && ($this->default_parameter != $parameter) ) {
			$this->default_parameter = $parameter;
			trace("Overwriting best suiting request parameter with '$parameter'", $this);
		} else {
			// do nothing
		}

        // a default value is given
        if ( (!is_null($value)) && (\Request::isEmpty($this->default_parameter)) ) {
            \Request::implant($this->default_parameter, $value);
            # FIXME -> maybe redirect.. only for beautiness in the uri string...
        }

        // check again if the default parameter value is empty. If so, take the default showparam from the configuration
        if (\Request::isEmpty($this->default_parameter)) {
            trace("Parameter value for ".$this->default_parameter." was empty, taking ".$this->config->get("default.showparam")." as default parameter", $this);
            $this->default_parameter = $this->config->get("default.showparam");
        }

		trace("Parameter value is '".\Request::getArgument($this->default_parameter)."'", $this);
        $this->config->import(array("default.parameter" => $this->default_parameter));

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



	public function executeSuitingRequestParameter() {
		switch ($this->default_parameter) {
			case "qf_controller":
				$this->processMVC(\Request::getArgument($this->default_parameter));
				break;
			case "qf_module":
				$this->processModule(\Request::GetArgument($this->default_parameter));
				break;
			default:
				$this->processMainContent(\Request::getArgument($this->default_parameter));
				break;
		}
	}

    protected function processMainContent($value) {
		trace("Adding main content", $this);
        if (file_exists(getFQPath($value, "module"))) {
            $this->processModule($value);
        } else {
            $rtpl = new \core\template\rainbow\RainbowTemplate();
#            $rtpl->registerPlugin(\core\plugins\xhtmlheader\XhtmlHeaderPlugin);
            $rtpl->draw($value);
        }
    }

   /**
    * processModule
    * Simply include a PHP file
	*
	* @param String $module The module in Quantum node representation
	* @return void
    */
    public function processModule($module) {
        $module_fq_path = getFQPath($module,"module");

        trace("Adding module '".$module."' from '".$module_fq_path."'",$this);

        if (file_exists($module_fq_path)) include $module_fq_path;
        else throw (new \core\exceptions\FileNotFoundException("Could not inherit module '$module' from $module_fq_path "));
    }

    public function processMVC($controller) {
        uses($controller);
        $controller_name = sprintf('\\%s', str_replace('.', '\\', $controller));

        try {
            $_controller = new $controller_name(array(
    			"condition"	=> $this->default_parameter,
    		));
        } catch (\Exception $e) {
            print "exception;";
            print $e;
        }

    }

    public function processAddon($addon, $alias, $datastore, $prototype) {
        $addon_name = ucfirst($addon)."Addon";
        $addon_uses_path = "addon.".$addon.".class.".$addon_name;
        trace("adding addon '".$addon_name."' (".$addon.") from '".$addon_uses_path."'", $this);
        uses ($addon_uses_path);

        array_push($this->addon_classes, $addon_class = New $addon_name($addon, $alias, $datastore, $prototype));
        $addon_class->run();
        $addon_class->dispatch();
    }

}

?>
