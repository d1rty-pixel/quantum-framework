<?php

uses ("core.base.mvc.BogusModel");
uses ("core.filesystem.File");
uses ("vendor.ConvertCharset.ConvertCharset");

class CsvgeneratorModel extends BogusModel {

    private $header_written     = false;
    private $charset            = null;
    private $quote_char         = '"';
    private $quote_array        = array();
    private $escape_char        = '"';
    private $escape_array       = array();
    private $sep_char           = ";";
    private $fs                 = null;

    public function init() {
        $this->fs = new File();
        $this->fs->setMode(FMODE_REWRITE);
    }

    public function setFile($file) {
        $this->fs->setFile($file);

        if ($this->fs->exists()) {
            $this->fs->delete();
        }
        $this->fs->create();
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

        $this->fs->write(implode($this->sep_char, array_values($this->handleQuoting($array)))); 
    }

    private function writeHeader($array) {
        $this->fs->write(implode($this->sep_char, array_values($this->handleQuoting(array_keys($array)))));
        $this->header_written = true;
    }

    public function close() {
        $this->fs->close();
    }

}

?>
