<?php

uses ("core.base.Quobject");

class HighchartPlotBands extends Quobject {

    private static $plotbands = null;

    public static function getWeekendPlotBands($start_ts, $end_ts, $label = null) {
        self::$plotbands = array();
        
        for ($i = $start_ts; $i <= $end_ts; $i += 86400) {
            $date = new DateTime();
            $date->setTimestamp($i);
            # saturday
            if ($date->format("N") == 6) {
                $date->modify("midnight");
                array_push(self::$plotbands, array(
                    "from"  => (Int) ($date->getTimestamp()."000"),
                    "to"    => (Int) (($date->getTimestamp() + 172799)."000"),
                    "color" => "rgba(190, 190, 190, .5)",
                    "label" => $label,
                ));
            }
        }
        return self::$plotbands;
    }

}

?>
