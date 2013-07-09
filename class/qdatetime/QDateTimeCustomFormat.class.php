<?php

namespace core\qdatetime;

uses ("core.base.Quobject");

class QDateTimeCustomFormat extends \core\base\Quobject {

    const DB_DATETIME       = "php://Y-m-d H:i:s";
    const DB_DATE           = "php://Y-m-d";
    const DB_TIME           = "php://H:i:s";
    const JS_TIMESTAMP      = "php://U000";

    private static $formats = array(
        "DB_DATETIME"       => \core\qdatetime\QDateTimeCustomFormat::DB_DATETIME,
        "DB_DATE"           => \core\qdatetime\QDateTimeCustomFormat::DB_DATE,
        "DB_TIME"           => \core\qdatetime\QDateTimeCustomFormat::DB_TIME,
        "JS_TIMESTAMP"      => \core\qdatetime\QDateTimeCustomFormat::JS_TIMESTAMP,
    );

    public static function get($name) {
        return self::$formats[strtoupper($name)];
    }
}

?>
