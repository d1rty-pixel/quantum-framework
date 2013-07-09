<?php

namespace core\filesystem;

uses (
    "core.base.Quobject",
    "core.exceptions.IllegalArgumentException"
);

class CSVFile extends \core\base\Quobject {

    private $header_written     = false;
    private $charset            = null;
    private $quote_char         = '"';
    private $quote_array        = array();
    private $escape_char        = '"';
    private $escape_array       = array();
    private $sep_char           = ";";

    public $_file                = null;

    public function __construct($file_object, $array = null) {
        parent::__construct();
        if (!is_object($file_object)) {
            throw new \core\exceptions\IllegalArgumentException("Parameter is not of type File");
        }

        $this->_file = $file_object;

        if (!is_null($array)) {
            if (isset($array["file"]))          $this->setFile($array["file"]);
            if (isset($array["quote_char"]))    $this->setQuoteChar($array["quote_char"]);
            if (isset($array["escape_char"]))   $this->setEscapeChar($array["quote_char"]);
            if (isset($array["sep_char"]))      $this->setSeperatorChar($array["sep_char"]);
        }
    }

    public function setQuoteChar($char) {
        $this->quote_char = $char;
    }

    public function setEscapeChar($char) {
        $this->escape_char = $char;
    }

    public function setSeperatorChar($char) {
        $this->sep_char = $char;
    }

    private function handleQuoting($array) {
        $escape_quote_chars = function($val, $quote_char, $escape_char) {
            return str_replace($quote_char, $escape_char.$quote_char, $val);
        };

        $quote_csv_values = function ($val, $quote_char) {
            return $quote_char.$val.$quote_char;
        };

        $array = array_map($escape_quote_chars, $array, $this->quote_array, $this->escape_array);
        $array = array_map($quote_csv_values, $array, $this->quote_array);

        return $array;
    }

    public function writeRecord($array) {
        if (!$this->header_written) {
            foreach ($array as $e) {
                array_push($this->quote_array,  $this->quote_char);
                array_push($this->escape_array, $this->escape_char);
            }
            $this->writeHeader($array);
        }
    
        $this->_file->write(implode($this->sep_char, array_values($this->handleQuoting($array))));
    }

    public function read() {
        return $this->_file->read();
    }

    public function close() {
        return $this->_file->close();
    }

    private function writeHeader($array) {
        $this->_file->write(implode($this->sep_char, array_values($this->handleQuoting(array_keys($array)))));
        $this->header_written = true;
    }

}

?>
