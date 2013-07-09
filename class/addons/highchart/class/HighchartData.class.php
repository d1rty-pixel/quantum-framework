<?php

uses ("core.base.Quobject", "core.exceptions.IllegalArgumentException", "addon.highchart.class.HighchartDataProperties");

class HighchartData extends Quobject {

    public $properties          = null;

    private $normalized         = false;

    private $config             = null;

    private $data               = array(
        "raw"                       => null,
        "normalized"                => null,
    );


    public function __construct($data = array(), $properties = array(), $config = array()) {
        parent::__construct();

        $this->properties = new HighchartDataProperties($properties);
        $this->applyConfig($config);

        if (!is_array($data)) 
            throw new IllegalArgumentException("data argument must be an Array");
        
        if (count($data) == 0) {
            warn("data argument array does not contain any spline data", $this);
        } else {
            if (!$this->is_flat($data))
                throw new IllegalArgumentException("data argument array is no flat array");

            $this->data["raw"] = $data;
            if ($this->config->fillgaps) $this->data["raw"] = $this->fillGaps($this->data["raw"]);
        }
    }


    private function applyConfig($config) {
        $this->config = (Object) array_merge($this->prototype(), (Array) $config);

        if (is_null($this->config->start) || !isset($this->config->start))
            throw new IllegalArgumentException("start is not defined in configuration");
        if (is_null($this->config->end) || !isset($this->config->end))
            throw new IllegalArgumentException("end is not defined in configuration");
        if (is_null($this->config->interval) || !isset($this->config->interval))
            throw new IllegalArgumentException("interval is not defined in configuration");

        return true;
    }

    public function getConfig($name = null) {
        if (is_null($name)) return $this->config;
        return $this->config->$name;
    }


    private function prototype() {
        return array(
            "start"         => Request::getArgument("start_ts"),
            "end"           => Request::getArgument("end_ts"),
            "interval"      => 86400,
            "fillgaps"      => true,
            "default"       => 0,
        );
    }

    private function is_flat($a) {
        $rv = array_filter($a, 'is_object');
        if (count($rv) > 0) return false;
        return true;
    }

    public function getData($raw = false) {
        return ($raw) ? $this->data["raw"] : $this->normalize($this->data["raw"]);
    }

    private function fillGaps($data) {
        trace(sprintf("Filling gaps for chart entity %s", $this->properties->getProperty("name")), $this);

        $format = QDateTime::getInputFormat(current(array_keys($data)));
        if (is_null($format) || $format == "") 
            throw new IllegalArgumentException(sprintf("Cannot determine date or time format for conversion on input '%s'", current(array_keys($data))));

        $format_method = sprintf("get%s", $format);
        debug(sprintf("Using format method %s for conversion", $format_method), $this);

        for ($i = $this->config->start; $i <= $this->config->end; $i += $this->config->interval) {
            $time = QDateTime::$format_method($i, "db");
            if (is_null($data[$time])) $data[$time] = $this->config->default;
        }

        ksort($data);
        return $data;
    }

    private function normalize($data) {
        if ($this->normalized && !is_null($this->data["normalized"])) return $this->data["normalized"];
        trace(sprintf("Normalizing data for chart entity %s", $this->properties->getProperty("name")), $this);

        $out = array();
        foreach ($data as $time => $value) {
            $mts = (Int) sprintf("%d000", QDateTime::getTimestamp($time));
            array_push($out, array($mts, (Float) $value));
        }

        $this->normalized = true;
        ksort($out);
        return $this->data["normalized"] = $out;
    }

    public function create() {
        if (!$this->normalized) $this->normalize($this->data["raw"]);

        $out = array(
            "data"          => $this->data["normalized"],
            "pointStart"    => sprintf("%d000", $this->config->start),
            "pointInterval" => sprintf("%d000", $this->config->interval),
        );
        foreach ((Array) $this->properties->getProperties() as $name => $value) {
            $out[$name] = $value;
        }
        return $out;
    }

}

?>
