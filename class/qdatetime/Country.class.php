<?php

namespace core\qdatetime;

uses (
    "core.base.Quobject"
);

class Country extends \core\base\Quobject {

    protected $name                 = null;

    protected $provinces            = array();

    protected $province             = null;

    protected $holidays             = null;

    protected $saturday_as_holiday  = true;

    protected $supported_vars       = array("name", "provinces", "province", "holidays", "saturday_as_holiday", "sunday", "saturday", "timezone");

    protected $sunday               = null;

    protected $saturday             = null;

    protected $timezone             = null;

    public function getProvinces() {
        return $this->provinces;
    }

    public function getProvince($name = null) {
        if (is_null($name))
            return $this->province;
        return $this->provinces->$name;
    }

    public function setProvince($province) {
        if (!in_array($province, array_keys((Array)$this->provinces))) throw new \core\exceptions\IllegalArgumentException(sprintf("Province %s not yet supported", $province));
        $this->province = $province;
    }

    public function SaturdayAsHoliday() {
        return $this->saturday_as_holiday;
    }

    public function getHolidays() {
        return $this->holidays;
    }

    public function getSaturday() {
        return $this->saturday;
    }

    public function getSunday() {
        return $this->sunday;
    }

    public function getTimezone() {
        return $this->timezone;
    }

}


?>
