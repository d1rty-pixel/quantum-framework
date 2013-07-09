<?php

namespace core\storages;

uses ("core.storages.Storage");

class ProjectXMLClassConfigurationStorage extends \core\storages\Storage {

    private $cm_params  = array();

    private function setOccurency($class, $method, $i, $value = null) {
        if ( (!array_key_exists($class, $this->cm_params)) || (!is_array($this->cm_params[$class])) )        $this->cm_params[$class] = array();
        if ( (!array_key_exists($method, $this->cm_params[$class])) || (!is_array($this->cm_params[$class][$method])) )
            $this->cm_params[$class][$method] = array();
        if ( (!array_key_exists($i, $this->cm_params[$class][$method])) || (!is_array($this->cm_params[$class][$method][$i])) )
            $this->cm_params[$class][$method][$i] = array();

        if (!is_null($value)) {
            array_push($this->cm_params[$class][$method][$i], $value);
        }
    }

    private function performConfiguration() {
        foreach ($this->cm_params as $class => $config) {
            \core\Quantum::registry("class_config_".$class, $this->getClassName());
            $this->$class($config);
            trace("Class $class has ".count($config)." method(s) for pre-configuration", $this);
        }
    }
    
    private function parseClassConfiguration($xml) {
        // cycle through all classes
        foreach (array_keys((Array) $xml[0]) as $class_name) {
            $this->cm_params[$class_name] = array();

            // hit all method names
            foreach (array_keys((Array) $xml[0]->$class_name) as $method_name) {
                $this->cm_params[$class_name][$method_name] = array();
                $i = 0;

                // this method has no attributes, there is only one value
                if (count((Array) $xml[0]->$class_name->$method_name->attributes()) == 0) {
                    $this->setOccurency($class_name, $method_name, $i, (String) $xml[0]->$class_name->$method_name);
                // there are attributes
                } else {
                    // cycle through all attributes
                    foreach ($xml[0]->$class_name->$method_name as $method_attributes) {
                        // get keys and values to add them to the parameter list 
                        foreach ($method_attributes->attributes() as $key => $val) {
                            // an empty value is interpreted as NULL
                            $this->setOccurency($class_name, $method_name, $i, (empty($val)) ? null : (String) $val);
                        }

                        $values = array_values((Array) $method_attributes);
                        // we have extra values that are not attributes
                        if (!@is_null($values[1])) {
                            $this->setOccurency($class_name, $method_name, $i, (String) $values[1]);
                        }
            
                        $i++;
                    } // foreach
                } // if
            } // foreach
        } // foreach
    }

	protected function init($xml) {
		// process Request class
		if ( (is_object($xml[0]->Request)) && (count ((Array) $xml[0]->Request) != 0) ) {
			$this->implantRequestParameters($xml[0]->Request);
			unset($xml[0]->Request);
		}
		// process Authentication class
		if (is_object($xml[0]->Authentication)) {
			$this->configureAuthenticationSettings($xml[0]->Authentication);
			unset($xml[0]->Authentication);
		}
		// process Maintenance class
		if (is_object($xml[0]->Maintenance)) {
			$this->configureMaintenance($xml[0]->Maintenance);
			unset($xml[0]->Maintenance);
		}

        $this->parseClassConfiguration($xml);
        $this->performConfiguration();
		$this->setReadOnly();
	}

	private function implantRequestParameters($xml) {
		foreach (@$xml->implants as $implants) {
			foreach ($implants as $implant) {
                # FIXME -> request type
				trace("Pre-Implanting request parameter '".(String) $implant->attributes()."' with value '".(String) $implant."'.", $this);
				Request::implant((String) $implant->attributes(), (String) $implant);
			}
		}
	}

	private function configureAuthenticationSettings($xml) {
		$this->authentication_class((String) $xml->class);
		$this->authentication_autocreate((Boolean) $xml->autocreate);
	}

	private function configureMaintenance($xml) {
		$this->maintenance_template	= (String) $xml->template;
		$this->maintenance_active	= ((String) $xml->active == "true") ? true : false;
	}

}

?>
