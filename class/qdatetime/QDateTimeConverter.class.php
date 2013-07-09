<?php

namespace core\qdatetime;

uses (
    "core.base.Quobject",
    "core.qdatetime.QDateTimeTimezone",
    "core.qdatetime.QDateTimeCustomFormat",
    "core.exceptions.IllegalArgumentException"
);

class QDateTimeConverter extends \core\base\Quobject {

    /**
        The default IntlDateFormatter date format (ICU)
    */
    private $date_format        = \IntlDateFormatter::MEDIUM;

    /**
        The default IntlDateFormatter time format (ICU)
    */
    private $time_format        = \IntlDateFormatter::MEDIUM;
    
    /**
        The custom format
        can be a ICU or PHP compatible string, can also be a \core\qdatetime\QDateTimeCustomFormat constanct
    */
    private $custom_format      = null;

    /**
        The custom format type, can be icu or php, defaults to icu
    */
    private $custom_format_type = "icu";

    /**
        i18n setting, defaults to de_DE
    */
    private $i18n               = "de_DE";

    /**
        The DateTimeZone object accessor
    */
    private $timezone           = null;

    /**
        The DateTime object accessor
    */
    private $datetime           = null;

    /**
        The IntlDateFormatter object accessor
    */
    private $formatter          = null;

    /**
        supported vars to get modified by json-configuration files by \core\base\Quoject
    */
    protected $supported_vars   = array("i18n", "custom_format_type");


    /**
        class constructor
        Creates DateTime, DateTimeZone and IntlDateFormatter objects
        Supports a wide varity of date/time descriptions including javascript-like millisecond timestamps, timezones and i18n settings from IntlDateFormatter
        date- and time formats can be specified from IntlDateFormatter
    */  
    public function __construct($datetime = "now", $i18n = null, $date_format = null, $time_format = null, $timezone = null) {
        # check if the given datetime is a timestamp (numeric arg), then add a '@' 
        if (is_numeric($datetime)) {
            # check if we have a javascript millisecond timestamp
            if (strlen($datetime) == 13) {
                $datetime = substr($datetime, 0, 10);
            }
            $datetime = sprintf("@%d", $datetime);
        }

        try {
            $this->timezone = new \core\qdatetime\QDateTimeTimezone($timezone);
            $this->datetime = new \DateTime($datetime, $this->timezone->get());
        } catch (\Exception $e) {
            throw new \core\exceptions\IllegalArgumentException($e);
        }

        if (!is_null($date_format)) $this->date_format = $date_format;
        if (!is_null($time_format)) $this->time_format = $time_format;

        $this->formatter = new \IntlDateFormatter(
            $this->i18n,
            $this->date_format,
            $this->time_format,
            $this->timezone->getTimezone()
        );
    }

    /**
        Sets the custom format, must be a string
        supports ICU project and php variables
    */
    public function setCustomFormat($format = null) {
        if (is_null($format)) {
            throw new \core\exceptions\IllegalArgumentException("custom datetime format must not be null");
        } else if (!is_string($format)) {
            throw new \core\exceptions\IllegalArgumentException("custom datetime format must be a string");
        } else {

            preg_match("/^(?:(php|icu):\/\/)?(.+)$/", $format, $format_matches);
            if (is_null($format_matches[1]) || $format_matches[1] == "") $format_matches[1] = "icu";
            if (is_null($format_matches[2]) || $format_matches[2] == "") throw new \core\exceptions\IllegalArgumentException("custom format string must not be null");

            $this->custom_format_type   = $format_matches[1];
            $this->custom_format        = $format_matches[2];

            if ($this->custom_format_type == "icu") {
                # note that the pattern must be a ICU-project compatible string, see http://userguide.icu-project.org/formatparse/datetime
                # this pattern differs from the default php date and time patterns
                $this->formatter->setPattern($this->custom_format);
            }
        }
    }

    /**
        Returns the converted date/time information with full support of custom formats, IntlDateFormatter i18n and timezone
    */
    public function get($format = null, $timezone = null) {

        if (!is_null($format)) $this->setCustomFormat($format);
        $dt_string = $this->formatter->format((Int) $this->datetime->format("U"));

        if (is_null($this->custom_format_type) || $this->custom_format_type == "icu") {
            return $dt_string;
        } else {
            if (!is_null($timezone)) {
                $timezone = new \DateTimeZone($timezone);
            } else {
                $timezone = $this->timezone->get();
            }
            $tmp_dt = new \DateTime($dt_string, $timezone);
            return $tmp_dt->format($this->custom_format);
        }
    }

    /**
        Returns the converted timestamp
    */
    public function getTimestamp() {
        return $this->datetime->format("U");
    }

    /**
        Returns the default timezone
    */
    public function getTimezone() {
        return $this->timezone->getTimezone();
    }

    /**
        Returns the default i18n settings
    */
    public function getI18N() {
        return $this->i18n;
    }

}

?>
