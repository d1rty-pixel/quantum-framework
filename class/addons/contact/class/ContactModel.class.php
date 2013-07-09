<?php

uses ("core.base.mvc.BogusModel");

class ContactModel extends BogusModel {

    private $fields     = array();

    private $format     = "xhtml";
    private $recipient  = null;
    private $header     = array();
    private $subject    = "Contact Formular Data received - Quantum Framework Application";

    public function init() {
        $this->header[] = "MIME-Version: 1.0";
    }

    public function setFormFields($data) {
        if (is_array($data)) {

        } else if ($data != "") {
            $this->fields = explode(",", $data);
        }
    }

    public function setFormat($format = "xhtml") {
        $this->format = $format;
        if ($format == "xhtml") {
            $this->header[] = "Content-Type: text/html; charset=utf-8";
        }
    }

    public function getFormat() {
        return $this->format;
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setRecipient($email) {
        $this->recipient = $email;
    }

    public function getRecipient() {
        return $this->recipient;
    }

    public function addToHeader($line) {
        $this->header[] = $line;
    }
    
    public function getHeader() {
        return implode("\r\n", $this->header);
    }

}

?>
