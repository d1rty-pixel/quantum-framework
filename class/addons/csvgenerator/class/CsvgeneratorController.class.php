<?php

uses ("core.base.mvc.Controller");

class CsvgeneratorController extends Controller {

    public function init() {
    }

    public function configure($array) {
        if (isset($array["file"]))          $this->model->setFile($array["file"]);
        if (isset($array["quote_char"]))    $this->model->setQuoteChar($array["quote_char"]);
        if (isset($array["escape_char"]))   $this->model->setEscapeChar($array["quote_char"]);
        if (isset($array["sep_char"]))      $this->model->setSeperatorChar($array["sep_char"]);
    }

    public function onGet() {
    }

    public function onPost() {
    }

}

?>
