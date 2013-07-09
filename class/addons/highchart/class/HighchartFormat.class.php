<?php

uses ("core.base.Quobject");

class HighchartFormat extends Quobject {

    protected static $date_format         = null;
    protected static $round_format        = null;
    protected static $tooltip_format      = null;

    public static function getDateFormat($format = null) {
        if (is_null($format)) {
            self::$date_format = "var days = Array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
var date = new Date(this.x);
var offset = date.getTimezoneOffset() * 60000;
if (offset < 0) { date.setTime(this.x + Math.abs(offset)); } else { date.setTime(this.x - Math.abs(offset)); }
var month = date.getUTCMonth() + 1;
var datestring = days[date.getUTCDay()]+', '+date.getUTCDate()+'.'+month+'.'+date.getUTCFullYear();";
        } else {
            self::$date_format = $format;
        }
        return self::$date_format;
    }
    
    public static function getRoundFormat($format = null) {
        if (is_null($format)) {
            self::$round_format = "var rounded = (Math.round(this.y * 100) / 100).toString();
rounded += (rounded.indexOf('.') == -1 ) ? '.00' : '00';";
        } else {
            self::$round_format = $format;
        }
        return self::$round_format;
    }
    
    public static function getTooltipFormat($suffix, $format = null) {
        if (is_null($format)) {
            self::$tooltip_format = "var string = datestring+' - <span style=\"font-weight: bold; color: '+this.series.color+'\">'+this.series.name+'</span>: <b>'+rounded.substring(0, rounded.indexOf('.') + 3)+'</b> $suffix';
return string;";
        } else {
            self::$tooltip_format = $format;
        }
        
        if (is_null(self::$date_format))   self::getDateFormat();
        if (is_null(self::$round_format))  self::getRoundFormat();
        return sprintf("function() { %s\n%s\n%s },", self::$date_format, self::$round_format, self::$tooltip_format);
    }

}

?>
