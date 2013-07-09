<?php

namespace core\i18n;

uses (
    "core.base.Quobject",
    "core.exceptions.IllegalArgumentException"
);

class Country extends \core\base\Quobject {

    protected $name             = null;

    private $province           = null;

    protected $provinces        = array();

    protected $holiday_rules    = array();

    protected $timezone         = null;

    protected $date_format      = null;

    public $holidays            = null;

    protected $supported_vars   = array("name", "provinces", "holiday_rules", "timezone", "date_format");


    public function getTimezone() {
        return $this->timezone;
    }

    public function getName() {
        return $this->name;
    }

    public function setProvince($province) {
        $province_id = (preg_match("/^[A-Z]+/", $province))
            ? $province
            : $this->getProvinceID($province);

        $province_name = $this->getProvinceName($province_id);

        $this->province = $province_id;
    }

    public function getProvince() {
        return $this->province;
    }

    public function getProvinces() {
        return $this->provinces;
    }

    public function getProvinceName($province_id) {
        if (!isset($this->provinces->$province_id))
            throw new \core\exceptions\IllegalArgumentException(sprintf("No such province %s in country %s",
                $province_id,
                $this->getClassName()
            ));

        return $this->provinces->$province_id;
    }

    public function getProvinceID($province_name) {
        foreach ($this->provinces as $id => $name) {
            if ($province_name == $name) return $id;
        }
        throw new \core\exceptions\IllegalArgumentException(sprintf("No such province %s in country %s",
            $province_name,
            $this->getClassName()
        ));
    }

    public function getHolidayData() {
        return $this->holiday_rules;
    }

    public function getDateFormat() {
        return $this->date_format;
    }

}

?>
