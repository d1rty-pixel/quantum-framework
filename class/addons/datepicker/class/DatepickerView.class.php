<?php

uses("core.base.mvc.View");
uses ("core.template.xhtml.SelectBox");

class DatepickerView extends View {

    private function displayIntervalSelector() {
        $box = new SelectBox();
        $box->setElementName("interval");
        $box->setElementID("dpintervalselect");
        foreach ($this->model->getIntervalSelections() as $name) {  
            $box->addOption($name, $name);
            if (Request::getArgument("interval") == $name) {
                $box->setSelectedOption($name);
            }
        }
        $box->create();
        return $box->getDocument();
    }

    public function display($type = "DatePicker", $config) {
        $this->template->addAddonTemplate("datepicker", "datepicker");

        # process hidden parameters (input type=hidden)
       # defaults are "start_ts", "end_ts", "show" and "stats".
        # more can be added in the extending DatePickerController with $config['parameters'] when creating a new object.
        $hidden_parameters = array_merge_recursive($config["parameters"], array("start_ts", "end_ts", "show"));

        foreach ($hidden_parameters as $parameter) {
            if (!Request::isEmpty($parameter)) {
                $this->template->setVar("datepicker", "hidden_input", array(
                    "HIDDEN_NAME"   => $parameter,
                    "HIDDEN_VALUE"  => (is_array(Request::getArgument($parameter))) ? implode(",", Request::getArgument($parameter)) : Request::getArgument($parameter),
                ));
            }
        }

        # add time when type = TimePicker
        if ($type == "TimePicker") {
            $this->template->setVar("datepicker", "is_timepicker_start", array());
            $this->template->setVar("datepicker", "is_timepicker_end", array());
        }

        # extra parameter
        $extra_params = "";
        foreach ($config["parameters"] as $param_name) {
            if (!Request::isEmpty($param_name)) {
                $extra_params .= "&".$param_name."=";
                $extra_params .= (is_array(Request::getArgument($param_name))) ? implode(",", Request::getArgument($param_name)) : Request::getArgument($param_name);
            }
        }

        # process buttons        
        if ($config["buttons"]) {
            $this->template->setVar("datepicker", "buttons", array());
            $this->template->setVar("datepicker", "prev_interval", $this->model->getPreviousIntervalData());
            $this->template->setVar("datepicker", "next_interval", $this->model->getNextIntervalData());
        }
        
        $this->template->parse("datepicker");

        # and the rest to replace
        $this->template->replace("%%ACTION_PATH_SCRIPT%%", (empty($config['action_path_script'])) ? "/index.php" : $config['action_path_script'] );
        $this->template->replace("%%INTERVAL%%", $this->displayIntervalSelector());
        $this->doReplaces($config["time_config"]["strings"]); 
    }           

	public function doReplaces($strings) {
        $strings = array(
            "start" => $this->model->getStrings("start"),
            "end"   => $this->model->getStrings("end")
        );

		$this->template->replace("%%STARTTIMEMS%%",Request::getArgument("start_ts")."000");
		$this->template->replace("%%ENDTIMEMS%%",Request::getArgument("end_ts")."000");
		$this->template->replace("%%STARTTIME%%",Request::getArgument("start_ts"));
		$this->template->replace("%%ENDTIME%%",Request::getArgument("end_ts"));

		$this->template->replace("%%START_HOUR%%",$strings["start"]["hour"]);
		$this->template->replace("%%START_MINUTE%%",$strings["start"]["minute"]);
		$this->template->replace("%%START_DATE%%",$strings["start"]["date"]);

		$this->template->replace("%%END_HOUR%%",$strings["end"]["hour"]);
		$this->template->replace("%%END_MINUTE%%",$strings["end"]["minute"]);
		$this->template->replace("%%END_DATE%%",$strings["end"]["date"]);

		$this->template->replace("%%SHOW%%",Request::getArgument("show"));

        $this->template->replace();
	}

}

?>
