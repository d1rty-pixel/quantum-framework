<?php

namespace core\filesystem;

uses (
    "core.filesystem.File",
    "core.exceptions.FileNotFoundException",
    "core.exceptions.IOException"
);

class BZFile extends \core\filesystem\File {

    public function __construct($file = null, $mode = FMODE_READ) {
        $this->setMode($mode);
        if (!is_null($file)) {
            $this->setFile($file);
        }
    }

    public function open($file = null) {
        if (!is_null($file)) $this->file = $file;

        trace("opening compressed file ".$this->file." with mode ".$this->mode, $this);

        if (false === $this->handle = bzopen($this->file, $this->mode)) {
            throw new \core\exceptions\FileNotFoundException("File ".$this->file." does not exist");
        }
    }

    public function write($string) {
        if (!$this->exists()) {
            trace("file ".$this->file." does not exist, creating", $this);
            $this->create();
        }

        if (!is_resource($this->handle)) {
            $this->open();
        }

        bzwrite($this->handle, $string.$this->lf, strlen($string.$this->lf));
        return true;
    }
 
    public function close() {
        if (is_resource($this->handle)) {
            trace("closing file ".$this->file,$this);
            bzclose($this->handle);
        }
    }

    public function read($bytes = 4096) {
        if (0 === $bytes) return "";

        if (false === ($result = bzread($this->handle, $bytes)) && (!$this->eof($this->handle)) ) {
            throw new \core\exceptions\IOException("Cannot read $bytes bytes from file '".$this->file."'");
        }

        return $result;
    }


}

?>
