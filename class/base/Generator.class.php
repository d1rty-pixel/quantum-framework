<?php

namespace core\base;

class Generator extends \core\base\Quobject {

    private $configuration  = null;
    private $random         = "";
    private $old            = array();

    public function configure($configuration = array()) {
        $this->configuration = $configuration;
        if (!array_key_exists("chars",      $this->configuration)) { $this->configuration["chars"]     = "0-9";    }
        if (!array_key_exists("amount",     $this->configuration)) { $this->configuration["amount"]    = 8;        }
        if (!array_key_exists("repeat",     $this->configuration)) { $this->configuration["repeat"]    = 1;        }
        if (!array_key_exists("delimeter",  $this->configuration)) { $this->configuration["delimeter"] = "-";      }
    }

    private function matches($stack, $min, $max) {
        return ( (preg_match("/[".(String) $stack."]/", $min)) && (preg_match("/[".(String) $stack."]/", $max)) );
    }

    private function _random($min, $max) {
        if ($this->matches("0-9", $min, $max)) {
            return rand($min, $max);
        } else if ($this->matches("a-zA-Z", $min, $max)) {
            return chr(rand(ord($min), ord($max)));
        }
    }
    
    public function generate() {
        $random = "";
        $count_chars = count($this->configuration["chars"]) - 1;
        for ($repeat = $this->configuration["repeat"]; $repeat >= 1; $repeat--) {

            for ($i = 1; $i <= $this->configuration["amount"]; $i++) {
                $selection  = $this->_random(0, $count_chars);
                $chars      = explode("-", $this->configuration["chars"][$selection]); 
                $random    .= $this->_random($chars[0], $chars[1]);
            }
            if ($repeat != 1) { $random .= $this->configuration["delimeter"]; }
        }

        if (in_array($random, $this->old)) {
            return $this->generate();
        } else {
            array_push($this->old, $random);
            $this->random = $random;
        }
        return $this->random;
    }

    public function getRandom() {
        $this->generate();
        return $this->random;
    }

}

?>
