<?php

class OutputProcessor extends Quobject {

    private $format = null;

    public function setOutputFormat($format = null) {
        $this->format = $format;
    }

    public function process($data) {
        if ($this->format == "json") {
            return json_encode($data);
        } else if ($this->format == "array") {
            return (Array) $data;
        }

        # return xml in other cases
    }

}

?>
