<?php

namespace core\i18n;

uses (
    "core.base.AbstractSingleton",
    "core.exceptions.IllegalArgumentException"
);

class HolidayTypes extends \core\base\AbstractSingleton {

    protected $default_type_id  = null;

    protected $types            = array();

    protected $supported_vars   = array("default_type_id", "types");

    public function getDefaultTypeID() {
        return $this->default_type_id;
    }

    public function getTypes() {
        return $this->types;
    }

    public function getTypeID($name) {
        foreach ($this->types as $type_id => $type_name) {
            if ($type_name == $name) return $type_id;
        }
        return null;
    }

    public function getTypeName($id) {
        foreach ($this->types as $type_id => $type_name) {
            if ($type_id == $id) return $type_name;
        }
        return null;
    }

    public function getTypeByID($id = null) {
        if (is_null($id)) $id = $this->default_type_id;
        if (!isset($this->types->$id))
            throw new \core\exceptions\IllegalArgumentException(sprintf("No such holiday type with id %d", $id));

        return (object) array(
            "id"    => $id,
            "name"  => $this->types->$id,
        );
    }

    public function getTypeByName($name) {
        $id = $this->getTypeID($name);

        return (object) array(
            "id"    => $id,
            "name"  => $this->types->$id,
        );
    }

}

?>
