<?php

namespace core\parser;

uses(
    "core.parser.Parser",
    "core.exceptions.IllegalArgumentException"
);

class JSONParser extends \core\parser\Parser {

    private $to_assoc       = false;

    private $error_map      = array(
        JSON_ERROR_DEPTH        => "Maximum stack depth exceeded",
        JSON_ERROR_CRTL_CHAR    => "Unexpected control charactor",
        JSON_ERROR_SYNTAX       => "Syntax error, malformed JSON string",
        JSON_ERROR_UTF8         => "Malformed UTF-8 characters, possibly incorrect encoded",
    );

    public function decode($data) {
        $result = json_decode($data, $this->to_assoc);

        switch(json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error =  ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = sprintf(' - Syntax error, malformed JSON in %s', $data);
                break;
            case JSON_ERROR_NONE:
            default:
                $error = '';                   
        }
        if (!empty($error))
            throw new \core\exceptions\IllegalArgumentException('JSON Error: '.$error);       
       
        return $result;
    }

    public function encode($data) {
        return xjson_encode($data);
    }

}

?>
