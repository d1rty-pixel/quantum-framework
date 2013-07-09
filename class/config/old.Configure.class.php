<?php

uses ("core.base.Quobject","core.base.Singleton","core.config.Config");
uses ("core.exceptions.FileNotFoundException","core.exceptions.GenericQuantumException");
uses ("core.config.ProjectXML");
uses ("core.storages.ProjectXMLClassConfigurationStorage");
uses ("core.data.DatabaseConnectionManager");

/**
 * Class Configure
 * Configures the framework and application
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base
 */
final class Configure extends Quobject {

   /**
    * @var Array
    * @access private
    */
	private $config		= null;

   /**
    * @var Array
    * @access private
    */
	private $_databases = array();

   /**
    * @var Object
    * @access private
    */
	private $_authentication = null;

   /**
    * @var Array
    * @access private
    */
	private $logfacilities = array();

	/**
	 * __construct
	 * The class constructor 
	 */
	function  __construct() {
		parent::__construct();
		// read the project.xml and configure the framework
		$project = new ProjectXML();

		$this->includeApplicationConfigurationFiles();
		$this->populateApplicationConfiguration();
	}

	/**
	 * includeApplicationConfigurationFiles
	 *
	 * Include *all* configuration files in CONFIG_PATH (as defined in your project-related index.php) that matches
	 * pattern /.config.php$/. Only $config[] is accepted here for configuring the application!
	 *
	 * FIXME opendir? wtf, ich hab ne filesystem klasse >.< benutz die gefälligst!
	 */
	private function includeApplicationConfigurationFiles() {
		trace("Including application configuration files", $this);
		if ($handle = @opendir(CONFIG_PATH)) {
		    while (false !== ($file = readdir($handle))) {
				if (preg_match("/.config.php$/",$file)) {
					include CONFIG_PATH.$file; 
					trace("Included file '".CONFIG_PATH.$file."'",$this);
				}
			}
		}
		$this->config = $config;
		$this->importConfiguration();
	}

	private function importConfiguration() {
		$config = Singleton::getInstance("Config");
		$config->import($this->config);
	}

	/**
	 * configureApplication
	 *
	 * Database, authentication and session object handlers are instanciated from here, according to the application configuration
	 */
	private function populateApplicationConfiguration() {
		Quantum::registry("use_database",$this->config['use']['database']);
		Quantum::registry("use_authentication",$this->config['use']['authentication']);
        Quantum::registry("use_logging", $this->config['use']['logging']);

		Quantum::registry("default_css",$this->config['default']['css']);
		Quantum::registry("default_showparam",$this->config['default']['showparam']);
		Quantum::registry("default_page",$this->config['default']['page']);

		Quantum::registry("path_modules",$this->config['path']['modules']);
		Quantum::registry("path_templates",$this->config['path']['templates']);
		Quantum::registry("path_css",$this->config['path']['css']);
		Quantum::registry("path_classes",$this->config['path']['classes']);

		Quantum::registry("maintenance",$this->config['maintenance']);
		Quantum::registry("maintenance_default",$this->config['maintenance_template']);

		Quantum::registry("hide",$this->config['hide']);
	
		if (Quantum::registry("use_database")) {
			$this->useDatabase();
		}

		if (Quantum::registry("use_authentication")) {
			$this->useAuthentication();
		}

        if (Quantum::registry("use_logging"))
            $this->useLogging($this->config["logging"]["file"], $this->config["logging"]["level"]);

		trace("Application configuration complete",$this);
	}
/*
    # DEPRECATED
	private function checkRequest() {
		$req_default_param = Quantum::registry("default_showparam");
		$req_default_value = Quantum::registry("default_page");
        $req_default_rewriterules = Quantum::registry("default_rewriterules");
		$show_param = Request::getArgument("show");

		if ( (empty($show_param)) || ($show_param == NULL) ) {
			if ( (!empty($req_default_param)) && (!empty($req_default_value)) ) {
				trace("redirecting because no show parameter given.",$this);

                if ($req_default_rewriterules) {
                    $location = $_SERVER['SCRIPT_URI'].$req_default_value;
                } else {
    				$location = $_SERVER['SCRIPT_URI']."index.php?".$req_default_param."=".$req_default_value;
                }
				Header("Location: ".$location);
			} else {
				throw (New GenericQuantumException("No default show parameter and/or value defined."));
			}
		}
	}
*/

	/**
	 * useDatabase
	 *
	 * the config-specific database object is instanced and database-specific configuration is stored to the registry
	 */
	private function useDatabase() {        
        $db_conn_manager = Singleton::getInstance("DatabaseConnectionManager");
		foreach (array_keys($this->config['databases']) as $dsn) {
            $db_conn_manager->registerInstance($dsn, $this->config['databases'][$dsn]);
		}
	}

	/**
	 * useAutbentication
	 *
	 * the authentication object is getting instanced.
	 */
	private function useAuthentication() {
		Quantum::registry("authentication_class",$this->config['authentication']['class']);
		Quantum::registry("authentication_sign",$this->config['authentication']['sign']);
		Quantum::registry("authentication_timeout",$this->config['authentication']['logintimeout']);

		$className = explode(".",Quantum::registry("authentication_class"));
		$className = $className[count($className) - 1];
		
		Quantum::registry("authentication_className",$className);
		uses ("core.base.Authentication");

		trace("using authentication module '".$className."'",$this);

		if ($this->config['authentication']['create']) {
			uses (Quantum::registry("authentication_class"));
			trace("creating authentication class '".$className."'",$this);
			$this->_authentication = new $className;
			Quantum::registry("auth",$this->_authentication->getClassName());
		}
	}
	
    private function useLogging($file, $level = "TRACE") {
        uses ("core.log.LogFacility");
        trace("Enabling logging in file $file at level $level", $this);
        $log_facility = Singleton::getInstance("LogFacility");
        $log_facility->setFile($file);
        $log_facility->setLevel($level);
    }

	public function post() {
    }
}

?>
