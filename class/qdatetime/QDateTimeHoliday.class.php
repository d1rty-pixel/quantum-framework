<?php

namespace core\qdatetime;

uses (
    "core.base.Quobject",
    "core.exceptions.IllegalArgumentException"
);


class QDateTimeHoliday extends \core\base\Quobject {

    protected $country          = null;

    protected $default_country  = "DE";

    protected $countries        = array();

    protected $supported_vars   = array("countries", "default_country");

    private $date               = null;


    private function initialize($date, $country = null, $province = null) {
        $this->setDate($date);
        $this->setCountry(  (!is_null($country))    ? $country  : $this->default_country);
        $this->setProvince( (!is_null($province))   ? $province : $this->country->getProvince());

        return $this->initializePredefinedDates(\QDateTime::get($this->date, "php://Y"));
    }

    private function initializePredefinedDates($year = null) {
        if (is_null($year))
            $year   = \QDateTime::get($this->date, "php://Y");

        return array(
            "EASTER_DATE"   => $this->getEasterDate($year),
            "YEAR"          => $year,
        );
    }

    public function setDate($date) {
        if (is_null($date)) throw new \core\exceptions\IllegalArgumentException("date must not be empty");

        if (is_object($date) && $date instanceof \DateTime) {
            $this->date = \QDateTime::getDate($date->format("U"));
        } else {
            $this->date = \QDateTime::getDate($date);
        }
    }

    public function setCountry($country) {
        if (!in_array($country, array_keys((Array) $this->countries)))  throw new \core\exceptions\IllegalArgumentException(sprintf("Country %s is not yet supported", $country));

        $class = $this->countries->$country;
        $class = substr(str_replace('\\', '.', $class), 1);

        uses($class);
        $this->country = new $this->countries->$country;
    }


    public function setProvince($province) {
        $this->country->setProvince($province);
    }

    public function getEasterDate($year) {
        return \QDateTime::getConverterObject(easter_date($year), null, null, null, $this->country->getTimezone())->get("php://Y-m-d");
    }

    public function getDayOfWeek($date) {
        $arr = getdate(\QDateTime::getTimestamp($date));
        return $arr["wday"];
    }

    public function isWeekend($date) {
        $dow = $this->getDayOfWeek($date);
        if ($dow == 0)
            return $this->country->getSunday();

        if ($dow == 6 && $this->country->SaturdayAsHoliday())
            return $this->country->getSaturday();

        return false;
    }

    private function returnObject($object) {
        $provinces = array();
        if (isset($object->provinces)) {
            foreach ($object->provinces as $province) {
                array_push($provinces, array("short" => $province, "name" => $this->country->getProvince($province)));
            }
        
        } else {
            foreach ($this->country->getProvinces() as $short => $long) {
                array_push($provinces, array("short" => $short, "name" => $long));
            }
        }

        return (Object) array(
            "name"      => $object->name,
            "date"      => \QDateTime::getDate($object->date),
            "provinces" => $provinces,
        );
    }

    public function isHoliday($date, $country = null, $province = null) {
        $predefined = $this->initialize($date, $country, $province);
        $date_ts = \QDateTime::getTimestamp($this->date);

        $holidays = $this->country->getHolidays();
        foreach ((Array) $holidays as $id => $holiday) {
            $holiday->date = str_replace(array_keys($predefined), array_values($predefined), $holiday->date);

            if ($date_ts == \QDateTime::getTimestamp($holiday->date)) {
                if (   isset($holiday->provinces) && 
                       is_array($holiday->provinces) && 
                       count($holiday->provinces) > 0
                ) {
                    if (!in_array($selected_province, $holiday->provinces))
                        continue;
                }
                return $this->returnObject($holiday);
            }
        }

        $is_weekend = $this->isWeekend($this->date);
        if (!is_bool($is_weekend)) {
            return $this->returnObject((Object) array(
                "name"  => $is_weekend,
                "date"  => $asking_date_ts,
            ));
        }

        return $this->returnObject((Object) array(
            "name"      => "Werktag",
            "date"      => $asking_date_ts,
        ));
    }

    public function getHolidays($year, $country = null, $province = null, $province_data = false) {
        $predefined = $this->initialize(sprintf("%s-01-01", $year), $country, $province);
        $holidays = array();

        foreach ($this->country->getHolidays() as $holiday) {
            if (    !is_null($province) &&
                    isset($holiday->provinces) && 
                    is_array($holiday->provinces) && 
                    count($holiday->provinces) > 0 && 
                    !in_array($province, $holiday->provinces)
            )
                continue;

            $holiday->date = \QDateTime::getDate(str_replace(array_keys($predefined), array_values($predefined), $holiday->date));
            if (!$province_data)
                unset($holiday->provinces);
            array_push($holidays, $holiday);
        }
        
        return $holidays;
    }

}

?>
