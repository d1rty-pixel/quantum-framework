<?php

uses ("core.base.Quobject", "core.exceptions.IllegalArgumentException", "core.exceptions.IllegalStateException");

class HighchartDataSeries extends Quobject {

    private $series = array();

    public function add($series) {
        if (!$series instanceof HighchartData) 
            throw new IllegalArgumentException("Series must be a HighchartData object");

        $name = $series->properties->getProperty("name");

        if (!is_null($this->series[$name])) 
            throw new IllegalStateException(sprintf("Data series '%s' already exists", $name));

        $this->series[$name] = $series;
    }

    public function getData($name, $raw = false) {
        if (is_null($name))     throw new IllegalArgumentException("Data series name must not be null");
        if (is_null($this->series[$name]))   throw new IllegalArgumentException(sprintf("Data series %s does not exists", $name));
        if (!$this->series[$name] instanceof HighchartData) throw new IllegalArgumentException(sprintF("Data series %s is no HighchartData object", $name));

        return $this->series[$name]->getData($raw);
    }

    public function getDataSeries($name = null, $raw = false) {
        if (!is_null($name)) return $this->getData($name, $raw);

        $out = array();
        foreach ($this->series as $name => $object) {
            array_push($out, ($raw) ? $this->getData($name, $raw) : $object->create());
        }
        return $out;
    }

    public function getDataSeriesObject($name) {
        if (is_null($name) || !$this->series[$name] instanceof HighchartData)
            throw new IllegalArgumentException("Data series name must not be null");

        return $this->series[$name];        
    }

    public function getDataSeriesNames() {
        return array_keys($this->series);
    }

    public function merge() {
        $objects = func_get_args();
        foreach ($objects as $series) {
            if (!$series instanceof HighchartDataSeries)
                throw new IllegalArgumentException("Cannot merge non-HighchartDataSeries objects");

            foreach ($series->getDataSeriesNames() as $name) {
                $this->add($series->getDataSeriesObject($name));
            }
        }
    }

}

?>
