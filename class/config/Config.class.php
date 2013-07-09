<?php

namespace core\config;

uses ("core.base.Quobject");

class Config extends \core\base\Quobject {

	private $config_array = NULL;

	public function import($array, $prefix = null) {
		$keys = array_keys($array);

		for ($i = 0; $i < count($array); $i++) {
			if ($i == 0)                      $position = current($array);
			else if (($i + 1) == count($array)) $position = end($array);
			else                                $position = next($array);

			if (is_array($position)) {
				$this->import($position, sprintf("%s.", $keys[$i]));
			} else {
				$this->config_array[sprintf("%s%s", $prefix, $keys[$i])] = $array[$keys[$i]];
			}
		}
	}

	public function get($key = null) {
        if (is_null($key)) return $this->config_array;
		return $this->config_array[$key];
	}

    public function getSimilar($like) {
        $matches = array();
        foreach (array_keys($this->config_array) as $key => $value) {
            if (preg_match_all("/".$like."/",$value,$out) != 0) {
                array_push($matches,$value);
            }
        }
        return $matches;
    }

}

?>
