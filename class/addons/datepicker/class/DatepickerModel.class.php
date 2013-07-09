<?php

uses("core.base.mvc.BogusModel", "core.base.Inflect");

class DatepickerModel extends BogusModel {

    private $config                         = null;
    private $defaults                       = null;

    private $interval_units                 = array(
        "hour",
        "day",
        "week",
        "month",
        "year",
    );
    private $interval_unit_limits           = array(
        "hour"      => 3600,            # 1 hour
        "day"       => 86400,           # 1 days
        "week"      => 604800,          # 1 week
        "month"     => 2592000,         # 30 days
        "year"      => 31536000,        # approx. 1 year
    );

    public function init() {
        $this->config = new stdClass();
        $this->defaults = new stdClass();
    }

    public function setDefaults() {
        # default values
        if (is_null($this->defaults->interval))     $this->defaults->interval   = "7 days";

        if ( (is_null($this->defaults->end_ts)) && (is_null($this->defaults->start_ts)) ) {
            $end = new DateTime();
            $end->setTime(23, 59, 59);
            $this->defaults->end_ts     = $end->getTimestamp();
            $this->defaults->start_ts = $this->defaults->end_ts - ($this->calcIntervalUnitTime($this->defaults->interval)) - 86399;

        } else if ( (is_null($this->defaults->end_ts)) && (!is_null($this->defaults->start_ts)) ) {
            $this->defaults->end_ts = $this->computeEndTS($this->defaults->start_ts, $this->defaults->interval);
        } else if ( (!is_null($this->defaults->end_ts)) && (is_null($this->defaults->start_ts)) ) {
            $this->defaults->start_ts = $this->computeStartTS($this->defaults->end_ts, $this->defaults->interval);
        }

        foreach (array("buttons", "parameters", "action_path_script", "interval") as $name) {
            $this->config->{$name} = $this->defaults->{$name};
        }
    }

    public function applyRequestParameters() {
        # 1. precedence have values from formular
        #  - if interval is not "user" take start + interval to calculate end, otherwise take start to end, set user interval
        if ( (!Request::isEmpty("end_date")) && (Request::getArgument("interval") == "user") ) {
            try {
                $end_time = Request::getArgument("end_time");
                if (empty($end_time)) $end_time = "23:59:59";
                $end = new DateTime(Request::getArgument("end_date")." ".$end_time);
                $this->config->end_ts = $end->getTimestamp();
            } catch (Exception $e) {
                $this->config->end_ts = $this->defaults->end_ts;
            }
        }

        if (!Request::isEmpty("start_date")) {
             try {
                $start_time = Request::getArgument("start_time");
                if (empty($start_time)) $start_time = "00:00:00";
                $start = new DateTime(Request::getArgument("start_date")." ".$start_time);
                $this->config->start_ts = $start->getTimestamp();
            } catch (Exception $e) {
                $this->config->start_ts = $this->defaults->start_ts;
            }
        }

        if ( (!Request::isEmpty("interval")) && (Request::getArgument("interval") != "user") ) {
            $this->config->interval = (!Request::isEmpty("interval")) ? Request::getArgument("interval") : $this->config->interval;
            ## look for start, precedence has start_ts, then 
            if (is_null($this->config->start_ts)) {
                $this->config->start_ts = (!Request::isEmpty("start_ts")) ? Request::getArgument("start_ts") : $this->defaults->start_ts;
            }
            $this->config->end_ts = $this->computeEndTS($this->config->start_ts, $this->config->interval);
        }

        if (is_null($this->config->end_ts)) {
            $this->config->end_ts   = (!Request::isEmpty("end_ts"))     ? Request::getArgument("end_ts")    : $this->defaults->end_ts; 
        }
        if (is_null($this->config->start_ts)) {
            $this->config->start_ts = (!Request::isEmpty("start_ts"))   ? Request::getArgument("start_ts")  : $this->defaults->start_ts; 
        }
    }

    public function set($key, $val) {
        $this->defaults->{$key} = $val;
    }
    
    public function getIntervalLimitUnitName($interval) {
        $last_unit = "";
        foreach ($this->interval_unit_limits as $unit => $multi) {
            if ($interval <= $multi) { return $unit; } else { $last_unit = $unit; continue; }
        }
        return $last_unit;
    }

    public function getIntervalUnits($value, $unit) {
        return $value * $this->interval_unit_limits[$unit];
    }

    public function calcIntervalUnitTime($string) {
        $a = explode(" ", $string, 2);
        if ( (!is_int($a[0])) && (!preg_match("/[a-z]*/", $a[1])) ) {
            $a = explode(" ", $this->defaults->interval, 2);
        }
        return $this->getIntervalUnits($a[0], Inflect::Singularize($a[1]));
    }

    public function computeStartTS($end_ts, $interval) {
        if (is_int($interval)) {
            return $end_ts - $interval;
        } else {
            $end = new DateTime(date("r", $end_ts));
            $int = explode(" ", $interval);
            $value = $int[0];
            $unit = Inflect::Singularize($int[1]);

            if ( ($unit == "day") || ($unit == "week") ) {
                return $end_ts - $this->getIntervalUnits($value, $unit);
    
            } else if ($unit == "month") {
                if (($end->format("m") - $value) < 1) {
                    $diff = explode(".", (String) (($end->format("m") - $value) / 12), 2); 
                    $year_minus = ($diff[0] < 0) ? $diff[0] * -1 : $diff[0];
                    $year_minus++;
                    $start_month    = (String) 12 - ((($end->format("m") - $value) * -1) % 12);
                    $start_year     = (String) $end->format("Y") - $year_minus;
                } else {
                    $start_month    = (String) $end->format("m") - $value;
                    $start_year     = (String) $end->format("Y");
                }

                $end->setDate($start_year, $start_month, $end->format("d"));                
            } else if ($unit = "year") {
                $end->setDate($end->format("Y") - $value, $end->format("m"), $end->format("d"));
            }
            return $end->getTimestamp();
        }
    }

    public function computeEndTS($start_ts, $interval) {
        if (is_int($interval)) {
            return $start_ts + $interval;
        } else {
            $int = explode(" ",$interval, 2);
            $value = $int[0];
            $unit = Inflect::Singularize($int[1]);
            $end = new DateTime(date("r", $start_ts));

            if ( ($unit == "day") || ($unit == "week") ){
                return $start_ts + $this->getIntervalUnits($value, $unit);

            } else if ($unit == "month") {
                if ($end->format("m") + $value > 12) {
                    $end_month = ( $end->format("m") + $value ) % 12;
                    $diff = explode(".", (String) ($end->format("m") + $value) / 12);
                    $end_year = $end->format("Y") + $diff[0];
                } else {
                    $end_month = $end->format("m") + $value;
                    $end_year = $end->format("Y");
                }

                $end->setDate($end_year, $end_month, $end->format("d"));
            } else if ($unit == "year") {
                $end->setDate($end->format("Y") + $value, $end->format("m"), $end->format("d"));
            }
            return $end->getTimestamp();
        }
    }

    public function getIntervalSelections() {
        $unit = Inflect::Singularize($this->getIntervalUnit($this->config->interval));
        switch ($unit) {
            case "hour":    return array("user", "24 hours"); break;
            case "day":     return array("user", "1 day", "2 days", "3 days", "4 days", "5 days", "6 days", "7 days"); break;
            case "week":    return array("user", "1 week", "2 weeks", "3 weeks", "4 weeks", "5 weeks", "6 weeks", "7 weeks", "8 weeks"); break;
            case "month":   return array("user", "1 month", "2 months", "3 months", "4 months", "5 months", "6 months", "7 months", "8 months", "9 months", "10 months", "11 months", "12 months"); break;
            case "year":    return array("user", "1 year", "2 years", "3 years", "4 years", "5 years", "10 years"); break;
        };
    }
	
    public function getConfig() {
        return $this->config;
    }

    public function getStrings($prefix) {
        $time = new DateTime(date("r", $this->config->{$prefix."_ts"}));
        return array(
            "date"  => $time->format("d.m.Y"),
            "hour"  => $time->format("H"),
            "minute"=> $time->format("i"),
            "second"=> $time->format("s"),
        );
    }

    private function getParameterString() {
        $extra_params = "";
        foreach ($this->config->parameters as $param_name) {
            if (!Request::isEmpty($param_name)) {
                $extra_params .= "&".$param_name."=";
                $extra_params .= (is_array(Request::getArgument($param_name))) ? implode(",", Request::getArgument($param_name)) : Request::getArgument($param_name);
            }
        }
        if (!Request::isEmpty("interval")) {
            $extra_params .= "&interval=".Request::getArgument("interval");
        }
        return $extra_params;
    }

    private function getIntervalUnit($interval) {
        $unit = explode(" ", $interval); return $unit[1];
    }

    private function getIntervalValue($interval) {
        $value = explode(" ", $interval); return $value[0];
    }

    public function getPreviousIntervalData() {
        return array(
            "param"     => $this->getParameterString(),
            "value"     => $this->getIntervalValue($this->config->interval),
            "unit"      => $this->getIntervalUnit($this->config->interval),
            "start_ts"  => $this->computeStartTS($this->config->start_ts, $this->config->interval),
            "end_ts"    => $this->config->start_ts,
        );
    }

    public function getNextIntervalData() {
        return array(
            "param"     => $this->getParameterString(),
            "value"     => $this->getIntervalValue($this->config->interval),
            "unit"      => $this->getIntervalUnit($this->config->interval),
            "start_ts"  => $this->config->end_ts,
            "end_ts"    => $this->computeEndTS($this->config->end_ts, $this->config->interval),
        );
    }

}

?>
