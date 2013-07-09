<?php

uses ("core.base.Generator");

function random($array) {
    $gen = \core\base\Singleton::getInstance('\core\base\Generator');
    $gen->configure($array);
    return $gen->getRandom();
}

function randomNumbers($array = array()) {
    if (!is_array($array["chars"]))     $array["chars"]     = array("0-9");
    return random($array);
}

function randomLetters($array = array()) {
    if (!is_array($array["chars"]))     $array["chars"]     = array("a-z");
    return random($array);
}

function randomChars($array = array()) {
    if (!is_array($array["chars"]))     $array["chars"]     = array("0-9", "a-z", "A-Z");
    return random($array);
}

function randomID($array = array()) {
    if (!array_key_exists("chars", $array))     $array["chars"]     = array("0-9", "a-z");
    if (!array_key_exists("amount", $array))      $array["amount"]    = 10;
    if (!array_key_exists("repeat", $array))      $array["repeat"]    = 4;
    return random($array);
}

?>
