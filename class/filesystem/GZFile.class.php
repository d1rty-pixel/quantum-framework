<?php

namespace core\filesystem;

uses ("core.filesystem.File");

class GZFile extends \core\filesystem\File {

    private $compress_level     = 9;
    private $compress_strategy  = null;

    public function __construct($file = null, $mode = FMODE_READ, $compress_level = 9, $compress_strategy = null) {
        $this->setMode($mode);
        if (!is_null($file)) {
            $this->setFile($file);
        }
        $this->setCompressLevel($compress_level);
        $this->setCompressStragety($compress_strategy);
    }

    public function setCompressLevel($level) {
        $this->compress_level = $level;
    }

    public function setCompressStragety($strategy = null) {
        $this->compress_strategy = $strategy;
    }

    public function open($file = null) {
        if (!is_null($file)) $this->file = $file;

        trace("opening compressed file ".$this->file." with mode ".$this->mode, $this);

        if (false === $this->handle = gzopen($this->file, $this->mode.$this->compress_level)) {
            throw (new \core\exceptions\FileNotFoundExceptio("File ".$this->file." does not exist"));
        }
    }

    public function eof() {
        return gzeof($this->handle);
    }

    public function write($string) {
        if (!$this->exists()) {
            trace("file ".$this->file." does not exist, creating", $this);
            $this->create();
        }

        if (!is_resource($this->handle)) {
            $this->open();
        }

        gzputs($this->handle, $string.$this->lf, strlen($string.$this->lf));
        return true;
    }
 
    public function close() {
        if (is_resource($this->handle)) {
            trace("closing file ".$this->file,$this);
            gzclose($this->handle);
        }
    }

    public function read($bytes = 4096) {
        if (0 === $bytes) return "";

        if (false === ($result = gzread($this->handle, $bytes)) && (!$this->eof($this->handle)) ) {
            throw (new \core\exceptions\IOException("Cannot read $bytes bytes from file '".$this->file."'"));
        }

        return $result;
    }


}

?>
