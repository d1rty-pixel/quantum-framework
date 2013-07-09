<?php

namespace core\qdatetime;

class QDateTimeTimezone extends \core\base\Quobject {

    private $timezone       = "UTC";
    private $tz             = null;

    protected $supported_vars = array("timezone");

    public function __construct($timezone = null) {
        $this->setTimezone($timezone);
    }

    public function setTimezone($timezone = null) {
        if (is_null($timezone)) $timezone = $this->timezone;
        try {
            $this->tz = new \DateTimeZone($timezone);
        } catch (\Exception $e) {
            throw new \core\exceptions\IllegalArgumentException(sprintf("Timezone %s is not a valid timezone description"));
        }
    }

    public function get() {
        return $this->tz;
    }

    public function getTimezone() {
        return $this->tz->getName();
    }

    public function getLocation() {
        return $this->tz->getLocation();
    }

    public function getTransitions() {
        return $this->tz->getTransitions();
    }

}

?>
