<?php

namespace core\i18n;

uses (
    "core.base.Quobject",
    "core.i18n.HolidayTypes"
);

class Holiday extends \core\base\Quobject {

    private $name           = null;

    private $date           = null;

    private $formatted_date = null;

    private $provinces      = null;

    private $types          = null;

    public function __construct($holiday_data, \core\i18n\Country $country) {
        parent::__construct();

        $this->name             = $holiday_data->name;
        $this->date             = $holiday_data->date;
        $this->formatted_date   = \QDateTime::get($holiday_data->date, $country->getDateFormat(), $country->getTimezone());
        $this->mapProvinces($holiday_data, $country);
        $this->mapHolidayTypes($holiday_data);
    }

    private function mapProvinces($holiday_data, $country) {
        $this->provinces = new \StdClass();
        foreach ($holiday_data->provinces as $province_id) {
            $this->provinces->$province_id = $country->getProvinceName($province_id);
        }

        if (count((array)$this->provinces) == 0) {
            $this->provinces = $country->getProvinces();
        }
    }

    private function mapHolidayTypes($holiday_data) {
        $types = \core\i18n\HolidayTypes::getInstance();
        $this->types = new \StdClass();

        if (count($holiday_data->types) == 0)
            $holiday_data->types = array($types->getDefaultTypeID());

        foreach ($holiday_data->types as $type_id) {
            $this->types->$type_id = $types->getTypeName($type_id);
        }
    }

    public function getDate() {
        return $this->date;
    }

    public function getFormattedDate() {
        return $this->formatted_date;
    }

    public function getName() {
        return $this->name;
    }

    public function getProvinces() {
        return $this->provinces;
    }

    public function getTypes() {
        return $this->types;
    }

}

?>
