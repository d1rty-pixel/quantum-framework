<?php

uses (
    "core.qdatetime.QDateTimeConverter",
    "core.qdatetime.QDateTimeHoliday"
);

if (!extension_loaded("intl")) {
    die("The intl Extension is not installed or not loaded");
}

class QDateTime {

    public static function getConverterObject($datetime = null, $i18n = null, $date_format = null, $time_format = null, $timezone = null) {
        return new \core\qdatetime\QDateTimeConverter($datetime, $i18n, $date_format, $time_format, $timezone);
    }

    public static function getHolidayObject($date = null, $country = null, $province = null) {
        return new \core\qdatetime\QDateTimeHoliday($date, $country, $province);
    }

    public static function getDate($datetime, $custom_format = null, $timezone = null) {
        return self::getConverterObject($datetime, null, null, \IntlDateFormatter::NONE, $timezone)->get($custom_format);
    }

    public static function getTime($datetime, $custom_format = null, $timezone = null) {
        return self::getConverterObject($datetime, null, \IntlDateFormatter::NONE, null, $timezone)->get($custom_format);
    }

    public static function getDateTime($datetime, $custom_format = null, $timezone = null) {
        return self::getConverterObject($datetime)->get($custom_format, $timezone);
    }

    public static function getTimestamp($datetime) {
        return self::getConverterObject($datetime)->getTimestamp();
    }

    public static function getTS($datetime) {
        return self::getTimestamp($datetime);
    }

    public static function get($datetime, $custom_format = null, $timezone = null) {
        return self::getConverterObject($datetime)->get($custom_format, $timezone);
    }

    public static function geti18n() {
        return self::getConverterObject(0)->getI18N();
    }

    public static function getTimezone() {
        return self::getConverterObject(0)->getTimezone();
    }

    public static function getHolidays($year, $country, $province) {

        #return new \core\qdatetime\QDateTimeHoliday()->getHolidays($year, $country, $province);
    }

    public static function isHoliday($date, $country, $province = null) {
        #return new \core\qdatetime\QDateTimeHoliday()->isHoliday($date, $country, $province);
    }

    public static function getHolidayInformation($date, $country, $province = null) {

    }

}

?>
