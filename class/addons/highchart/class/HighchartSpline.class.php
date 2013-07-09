<?php

uses ("core.base.Quobject", "core.exceptions.IllegalArgumentException");

class HighchartSpline extends Quobject {

    private $spline_config      = null;
    private $spline             = null;

    private $normalized         = false;
    private $converted          = false;
    private $created            = false;

    public function __construct($data, $config) {
        parent::__construct();

        $this->applyConfig($config);

        if (!is_array($data)) {
            throw new IllegalArgumentException("data argument must be an Array");
        }
        if (count($data) == 0) {
            warn("Data array does not contain any spline data", $this);
        } else {
            if (!$this->is_flat($data))
                throw new IllegalArgumentException("data argument array is no flat array");

            $this->spline = $data;
            $this->spline = ($this->spline_config->normalize)   ? $this->normalize($this->spline)   : $this->spline;
            $this->spline = ($this->spline_config->fillnulls)   ? $this->fillNull($this->spline)    : $this->spline;
            $this->spline = ($this->spline_config->convert)     ? $this->convert($this->spline)     : $this->spline;
            $this->spline = ($this->spline_config->create)      ? $this->create($this->spline)      : $this->spline;
        }
    }

    private function applyConfig($config) {
        $this->spline_config = (Object) array_merge($this->prototype(), (Array) $config);

        if (is_null($this->spline_config->start) || !isset($this->spline_config->start))
            throw new IllegalArgumentException("start is not defined in configuration");
        if (is_null($this->spline_config->end) || !isset($this->spline_config->end))
            throw new IllegalArgumentException("end is not defined in configuration");
        if (is_null($this->spline_config->interval) || !isset($this->spline_config->interval))
            throw new IllegalArgumentException("interval is not defined in configuration");

        return true;
    }

    private function prototype() {
        return array(
            "type"          => "spline",
            "visible"       => true,
            "name"          => sprintf("Unnamed Series %s", randomID()),
            "data"          => array(),
            "date_field"    => "report_date",
            "create"        => true,
        );
    }

    private function is_flat($a) {
        $rv = array_filter($a, 'is_object');
        if (count($rv) > 0) return false;
        return true;
    }

    private function normalize($data) {
        trace("Normalizing spline data", $this);
        $this->normalized = true;

        foreach ($data as $time => $value) {
            $ntime = (Int) sprintf("%d000", QDateTime::getTimestamp($time));
            $data[$ntime] = $data[$time];
            unset($data[$time]);
        }

        return $data;
    }

    private function fillNull($data) {
        trace("Filling null values", $this);
        for ($i = $this->spline_config->start; $i <= $this->spline_config->end; $i += $this->spline_config->interval) {
            $mts = (Int) sprintf("%d000", $i);
            if (is_null($data[$mts])) $data[$mts] = $this->spline_config->default;
        }


        ksort($data);
        return $data;
    }

    private function convert($data) {
        $out = array();
        $this->converted = true;

        foreach ($data as $ts => $value) {
            $out[] = array($ts, $value);
        }
        return $out;
    }

    private function create($data) {
        if (!$this->normalized && !$this->converted)
            throw new IllegalStateException("Cannot create unnormalized/unconverted spline data");

        $this->created = true;
        return array(
            "name"          => $this->spline_config->name,
            "visible"       => $this->spline_config->visible,
            "type"          => $this->spline_config->type,
            "data"          => $data,
            "pointStart"    => sprintf("%d000", $this->spline_config->start),
            "pointInterval" => sprintf("%d000", $this->spline_config->interval),
        );    
    }

    public function addPrepared($data) {
        if (!$this->is_flat($data))
            throw new IllegalArgumentException("data argument array is no flat array");

        $this->normalized = true;
        $this->converted = true;

        print_r($data);

        return $this->create($data);
    }

    public function getName() {
        return $this->spline["name"];
    }

    public function getSpline() {
        return $this->spline;
    } 

}

?>
