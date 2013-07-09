<?php

function iround($zahl_anf, $nachkommastellen) {
    $zahl_anf = round($zahl_anf,$nachkommastellen);
    $zahl = explode (".",$zahl_anf);
    if(isset($zahl[1])) {
        if(strlen($zahl[1]) < $nachkommastellen) {
            $kommastellen = $zahl[1];
            $anzahlnullen = 0;
            while(strlen($kommastellen) < $nachkommastellen) {
                $kommastellen = $kommastellen."0";
                $anzahlnullen++;
            }
            for($i=0;$i<$anzahlnullen;$i++) {
                $ausgabe = $zahl_anf."0";
            }
        } else {
            $ausgabe = $zahl_anf;
        }
    } else {
        $ausgabe = $zahl[0];
        $ausgabe = $zahl_anf.".00";
    }

    return $ausgabe;
}

?>
