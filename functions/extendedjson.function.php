<?php

## took from http://php.net/manual/de/function.json-encode.php and modified

function xjson_encode($input = array(), $funcs = array(), $level = 0) {
    foreach($input as $key=>$value) {
        if (is_array($value)) {
            $ret = xjson_encode($value, $funcs, 1);
            $input[$key] = $ret[0];
            $funcs = $ret[1];
        } else {
            if ( (is_object($value)) && ($value instanceof HighchartJson) ) {
                $func_key = "#".uniqid()."#";
                $funcs[$func_key]   = $value->get();
                $input[$key]        = $func_key;
            }
        }
    }
    if ($level==1) {
        return array($input, $funcs);
    } else {
        if ( (is_object($input)) && ($input instanceof HighchartJson) ) {
            return $input->get();
        } else {
            $input_json = json_encode($input);
            foreach($funcs as $key => $value) {
                $input_json = str_replace('"'.$key.'"', $value, $input_json);
            }
            return $input_json;
        }
    }
} 

?>
