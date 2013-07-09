<?php

uses ("core.base.Quobject", "core.base.Singleton");
uses ("core.exceptions.FileNotFoundException","core.exceptions.GenericQuantumException", "core.exceptions.XMLException");
uses ("core.storages.ProjectXMLClassConfigurationStorage");

/**
 * Automatically read the project.xml and set preferences regarding the settings in the XML
 * Also creates the first-time instance of the cli-depending Request class
 *
 * @package core.config
 * @author Tristan Cebulla <equinox@lichtspiele.org>
 * @copyright Tristan Cebulla
 * @version 1.0
 */
final class ProjectXML extends Quobject {

   /**
	* @var SimpleXMLObject
	* @access private
	*/ 	 
	private $project_xml				= null;
   /*
	* @var String
	* @access private
	*/
	private $project_name		= null;
   /*
	* @var String
	* @access private
	*/
	private $project_domain		= null;
   /*
	* @var String
	* @access private
	*/
	private $project_docroot	= null;
   /*
	* @var Array
	* @access private
	*/
	private $project_scriptlets	= array();
   /*
	* @var Array
	* @access private
	*/
	private $project_output		= array();
   /*
	* @var Boolean
	* @access private
	*/
	private $request_loaded		= false;


   /**
	* __construct
	* The class constructor 
	*
	* @param String $xml_file The location of the project xml file
	* @param Boolean $autorum Run the configuration (defaults to true)
	* @access
	* @return
	* @see autodetectProjectProtocol
	* @see loadRequestClass
	* @see core.base.RequestBase
	* @see init
	*/	
	public function __construct($xml_file = null) {
        $protocol = $this->autodetectProjectProtocol();
   
        # still empty?
        if (empty($protocol)) {
            throw (new GenericQuantumException("Unable to determine protocol."));
        }

		$this->project_scriptlets["protocol"] = $protocol;
		$this->loadRequestClass($protocol);

		// cli specific 
		if ($this->project_scriptlets["protocol"] == "cli") {
			if (Request::isEmpty("project_xml")) throw (new GenericQuantumException("CLI-Parameter 'project_xml' missing."));
			// take the location to the project xml file from request parameters
			if (is_null($xml_file)) {
				$xml_file = Request::getArgument("project_xml");
			}
		// http/https specific
		} else {
			$xml_file = APP_ROOT."/project.xml";
		}

		$this->init($xml_file);
		// populate configuration in the Quantum registry
    	$this->populateConfiguration();

		if (!is_null($this->project_xml["classes"])) {
			$this->storeClassConfiguration();
		}
	}

   /**
	* init
	* Reads the project xml file, determins the project and sets documentroot as well as the scriptlets and output settings
	*
	* @param String $xml_file The location of the project xml file
	* @access private
	* @return void
	* @see determineProject
	*/	
	private function init($xml_file) {
        trace("Initializing", $this);
		// try to load the xml file
		if (!file_exists($xml_file)) {
			throw (new FileNotFoundException("Could not open project XML file '$xml_file'."));
		}

		if (false === $xml = simplexml_load_file($xml_file)) {
			throw (new XMLException("Could not load project XML file '$xml_file'."));
		}
		trace("Loaded project xml file '$xml_file'", $this);
	
		// protocol is cli
		if ($this->project_scriptlets["protocol"] == "cli")	{
			// if the request parameter 'project_domain' is set use this, otherwise 'cli'
			$search_domain = (!Request::isEmpty("project_domain")) ? Request::getArgument("project_domain") : "cli";
		// protocol is other than cli, domain can be identified by the SERVER_NAME or HTTP_HOSt environment variables
		} else {
			$search_domain = getenv("SERVER_NAME");
            # or retry with HTTP_HOST environment variable
            if (empty($search_domain)) {
                $search_domain = getenv("HTTP_HOST");
            }
		}

		// try to determine the project
		if (!$this->determineProject($xml, $search_domain)) {
			throw (new GenericQuantumException("Domain ".$search_domain." is not configured within the project XML file"));
		}

		// set project settings
		$this->project_docroot		= (String) $this->project_xml["docroot"];
		$this->project_scriptlets	= (Array) $this->project_xml["scriptlets"];
		$this->project_output		= (Array) @$this->project_xml["output"];

		// define the BASE_URI constant
		define("BASE_URI", $this->project_scriptlets["protocol"]."://".$this->project_domain."/");
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
	private function loadRequestClass($protocol = null) {
		// proceed id the request class has not been loaded already
		if (!$this->request_loaded) {
			if (is_null($protocol)) {
                throw (new GenericQuantumException("Parameter protocol must not be null"));
            }

			trace("Loading Request class of protocol type '$protocol'" ,$this);
			uses ("core.protocol.".$protocol.".Request");
			$_request = Singleton::getInstance("Request");

			// set request_loaded true to avoid more loading
			$this->request_loaded = true;
		}
	}

   /**
	* populateConfiguration
	* Registers the configuration to the Quantum registry
	*
	* @access private
	* @return Void
	*/	
	private function populateConfiguration() {
		trace("populating configuration to Quantum registry", $this);
        Quantum::registry("project_protocol",		$this->project_scriptlets["protocol"]);
        Quantum::registry("project_output",			$this->project_scriptlets["content"]);	## FIXME unschoen project_output .. muss man aber viel aendern
        Quantum::registry("project_lang",			(!@is_null($this->project_output["lang"])) ? $this->project_output["lang"] : "default");
		Quantum::registry("project_container",		(!@is_null($this->project_output["container"])) ? $this->project_output["container"] : "default");
		Quantum::registry("project_output_display",	(!@is_null($this->project_output["display"])) ? $this->project_output["display"] : true);
        Quantum::registry("project_docroot",		$this->project_docroot);
        Quantum::registry("project_domain",			$this->project_domain);
	}

   /**
	* autodetectProjectProtocol
	* Autodetects the project protocol scriptlet using simple techniques to identify the protocol
	*
	* @access private
	* @return void
	*/	
	private function autodetectProjectProtocol() {
        $protocol = "cli";

        $http_host = getenv("HTTP_HOST");
		if (!empty($http_host)) {
			$protocol = "http";
		} ## FIXME https ?

		trace("Autodetected project protocol scriptlet as '".$protocol."'", $this);
        return $protocol;
	}

   /**
	* determineProject
	* Determine the project XML from the full XML. Returns true if the domain could be found in one of the projects
	*
	* @param SimpleXMLObject $xml The complete XML of all projects
	* @param String $search_domain The domain to search for
	* @access private
	* @return Boolean
	*/	
	private function determineProject($xml, $search_domain = null) {
        trace("Determining project in xml for domain '$search_domain'", $this);
        if ( (empty($search_domain)) || (is_null($search_domain)) ) {
            throw (new GenericQuantumException("Search domain must not be empty."));
        }

        $projects = $xml->xpath("/projects/project");
        $current_project_xml    = null;
        $project_found          = false;

		// cycle through alle projects
        foreach ($projects as $_string => $project_xml) {
			// set the project name and xml string 
            $current_project_xml =& $project_xml;

			// cycle through all domains of this project
            foreach ($project_xml->xpath("domains") as $_id => $domains) {
                foreach ($domains as $_id => $domain) {
					// set the project_domain if it matches
                    if ($search_domain == $domain) {
                        trace("Project domain matched ($domain)", $this);
                        $project_found = true;
                        break;
					// or go on
					} else {
                        continue;
                    }
                }
                if ($project_found) {
                    break;
                }
            }
            if ($project_found) {
                break;
            }

		}

        
        $this->project_name = $current_project_xml->attributes();
        $this->project_domain = $search_domain;
        $this->project_xml = (Array) $current_project_xml;
        return $project_found;
	}	

	private function storeClassConfiguration() {
		$storage = Singleton::getInstance("ProjectXMLClassConfigurationStorage", @$this->project_xml["classes"]);
	}

}

?>
