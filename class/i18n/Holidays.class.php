<?php

namespace core\i18n;

uses (
    "core.base.Quobject",
    "core.i18n.Holiday",
    "core.i18n.HolidayTypes",
    "core.exceptions.IllegalArgumentException"
);


class Holidays extends \core\base\Quobject {

    protected $default_country  = null;

    protected $supported_vars   = array("default_country");

    private $country            = null;

    private $holiday_types      = null;

    private $date               = null;


    public function __construct($country = null, $province = null) {
        parent::__construct();

        if (is_null($country)) {
            uses ($this->default_country);
            $country_class = sprintf('\%s', str_replace('.', '\\', $this->default_country));
            $country = new $country_class();
        } else {
            if (!$country instanceof \core\i18n\Country)
                throw new \core\exceptions\IllegalArgumentException('country must be null or a \core\i18n\Country object');
        }

        $this->country          = $country;
        if (!is_null($province))
            $this->country->setProvince($province);

        $this->holiday_types    = \core\i18n\HolidayTypes::getInstance();
    }

    private function initializePredefinedDates($year = null) {

        if (is_null($year) || !preg_match("/^\d{4}$/", $year))
            $year   = \QDateTime::get($this->date, "php://Y", $this->country->getTimezone());

        return array(
            "EASTER_DATE"   => $this->getEasterDate($year),
            "YEAR"          => $year,
        );
    }

    private function setDate($date) {
        if (is_null($date)) throw new \core\exceptions\IllegalArgumentException("date must not be empty");

        if (is_object($date) && $date instanceof \DateTime) {
            $this->date = \QDateTime::getDate($date->format("U"), null, $this->country->getTimezone());
        } else {
            $this->date = \QDateTime::getDate($date, null, $this->country->getTimezone());
        }
    }

    public function getEasterDate($year = null) {
        if (is_null($year)) $year = \QDateTime::get("today", "php://Y", $this->country->getTimezone());

        return \QDateTime::getConverterObject(easter_date($year), null, null, null, $this->country->getTimezone())->get("php://Y-m-d");
    }

    public function getDayOfWeek($date = null) {
        if (is_null($date)) $date = $this->date;

        $arr = getdate(\QDateTime::getTimestamp($date));
        return $arr["wday"];
    }

    public function isWeekend($date = null) {
        if (is_null($date)) $date = $this->date;

        $dow = $this->getDayOfWeek($date);
        if ($dow == 0 || $dow == 6) return true;
        return false;
    }

    private function isInDaysRange($days_range, $day = null) {
        if (    is_null($days_range) ||
                !is_array($days_range) ||
                (is_array($days_range) && count($days_range) == 0)
        )
            return true;

        if (is_null($day))
            $day = $this->getDayOfWeek(\QDateTime::get("now"));

        if (!preg_match("/^[0-6]$/", $day))
            $day = $this->getDayOfWeek($day);
        
        return (in_array($day, $days_range));
    }

    private function isInYearRange($year_range, $year = null) {
        if (is_null($year))
            $year = \QDateTime::get("now", "php://Y");

        $range = list($start, $end) = split("-", $year_range);

        if (!isset($range[1])) $range[1] = $range[0];
        if (!preg_match("/^\d{4}$/", $range[0]))
            $range[0] = \QDateTime::get(sprintf("01.01.%s", $range[0]), "php://Y");
        if (!preg_match("/^\d{4}$/", $range[1]))
            $range[1] = \QDateTime::get(sprintf("01.01.%s", $range[1]), "php://Y");
    
        return ($range[0] <= $year && $year <= $range[1]);
    }

    private function isInTypesRange($types = null, $search_types = null) {
        if (    is_null($search_types) || 
                (is_array($search_types) && count($search_types) == 0) ||
                is_null($types) ||
                (is_array($types) && count($types) == 0)
        ) return true;

        return (count(array_intersect($types, $search_types)) != 0);
    }

    private function isInProvinceRange($provinces, $search_province) {
        if (is_null($search_province) || is_null($provinces))  return true;

        return (    is_array($provinces) &&
                    count($provinces) > 0 &&
                    in_array($search_province, $provinces)
        );
    }


    public function getHolidayData($date, $province = null, $types = array()) {
        try {
            $this->setDate($date);
        } catch (\core\exceptions\IllegalArgumentException $e) {
            $this->setDate(\QDateTime::getDate("now", null, $this->country->getTimezone()));
        }

        if (!is_null($types) && !is_array($types)) {
            $types = array($types);
        }

        $date_ts        = \QDateTime::getTimestamp($this->date);
        $predefined     = $this->initializePredefinedDates(\QDateTime::get($this->date, "php://Y", $this->country->getTimezone()));
        $holidays       = $this->country->getHolidayData();
        $holiday_data   = array();

        foreach ((Array) $holidays as $id => $holiday) {
            $holiday->date = str_replace(array_keys($predefined), array_values($predefined), $holiday->date);

            if ($date_ts == \QDateTime::getTimestamp($holiday->date)) {

                # fix holiday type
                if (!isset($holiday->types))
                    $holiday->types = (array) $this->holiday_types->getTypeByID();  # gets the default type

                # check for province
                if (    isset($holiday->provinces) &&
                        !is_null($province) &&
                        !$this->isInProvinceRange($holiday->provinces, $province)
                )
                    continue;

                # check for days
                if (    isset($holiday->days) &&
                        !$this->isInDaysRange($holiday->days, $date_ts)
                )
                    continue;

                # check for types
                if (    isset($holiday->types) &&
                        count($types) > 0 &&
                        !$this->isInTypesRange($holiday->types, $types)
                )
                    continue;

                # check for years
                if (    isset($holiday->years) &&
                        is_array($holiday->years) &&
                        count($holiday->years) > 0
                ) {
                    foreach ($holiday->years as $range) {
                        if ($this->isInYearRange($range, $predefined["YEAR"])) {
                            array_push($holiday_data, new \core\i18n\Holiday($holiday, $this->country));
                        } else {
                            continue;
                        }
                    }
                } else {
                    array_push($holiday_data, new \core\i18n\Holiday($holiday, $this->country));
                }
            }
        }

        return $holiday_data;
    }


    public function isHoliday($date) {
        return (count($this->getHolidayData($date)) > 0);
    }

    public function getHolidays($year = null, $province = null, $types = array()) {
        if (is_null($year) || !preg_match("/^\d{4}$/", $year))
            $year = \QDateTime::get("now", "php://Y", $this->country->getTimezone());

        $predefined = $this->initializePredefinedDates($year);
        $holidays   = $this->country->getHolidayData();
        $holiday_data = array();

        foreach ($holidays as $holiday) {

            # check for province
            if (    isset($holiday->provinces) &&
                    !is_null($province) &&
                    !$this->isInProvinceRange($holiday->provinces, $province)
            )  
                continue;

            # check for types
            if (    isset($holiday->types) &&
                    count($types) > 0 &&
                    !$this->isInTypesRange($holiday->types, $types)
            )
                continue;

            # calculate the specific date for this holiday date
            $holiday->date = \QDateTime::getDate(
                str_replace(array_keys($predefined), array_values($predefined), $holiday->date),
                null,
                $this->country->getTimezone()
            );

            # check for days
            if (    isset($holiday->days) &&
                    !$this->isInDaysRange($holiday->days, $holiday->date)
            )
                continue;

            # check for years
            if (    isset($holiday->years) &&
                    is_array($holiday->years) &&
                    count($holiday->years) > 0
            ) {
                foreach ($holiday->years as $range) {
                    if ($this->isInYearRange($range, $year)) {
                        array_push($holiday_data, new \core\i18n\Holiday($holiday, $this->country));
                    } else {
                        continue;
                    }
                }
            } else {
                array_push($holiday_data, new \core\i18n\Holiday($holiday, $this->country));
            }
        }

        return $holiday_data;
    }

}

?>
