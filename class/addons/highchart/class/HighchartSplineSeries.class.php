<?php

uses ("core.base.Quobject", "core.exceptions.IllegalArgumentException", "core.exceptions.IllegalStateException");

class HighchartSplineSeries extends Quobject {

    private $series = array();

    public function add($series) {
        if (!$series instanceof HighchartSpline) 
            throw new IllegalArgumentException("Series must be a HighchartSpline object");

        if (!is_null($this->series[$series->getName()])) 
            throw new IllegalStateException(sprintf("Spline series %s already exists", $series->getName()));

        $this->series[$series->getName()] = $series;
    }

    public function getSeries($name = null, $pure_spline = true) {
        if (!is_null($name) && $this->series[$name] instanceof HighchartSpline) return ($pure_spline) ? $this->series[$name]->getSpline() : $this->series[$name];

        $out = array();
        foreach ($this->series as $name => $object) {
            array_push($out, ($pure_spline) ? $object->getSpline() : $object);
        }
        return $out;
    }

    public function merge() {
        $objects = func_get_args();
        foreach ($objects as $series) {
            if (!$series instanceof HighchartSplineSeries)
                throw new IllegalArgumentException("Cannot merge non-HighchartSplineSeries objects");

            foreach ($series->getSeries(null, false) as $spline) {
                $this->add($spline);
            }
        }
    }

}

?>
