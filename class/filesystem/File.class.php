<?php

namespace core\filesystem;

uses ("core.base.Quobject", "core.filesystem.FS", "core.exceptions.OperationNotPermittedException");

define ("FMODE_READ",       "r");
define ("FMODE_READWRITE",  "r+");
define ("FMODE_WRITE",      "w");
define ("FMODE_REWRITE",    "w+");
define ("FMODE_APPEND",     "a");
define ("FMODE_READAPPEND", "a+");

class File extends \core\base\Quobject {

    protected $handle           = null;
    protected $file             = null;
    protected $mode             = FMODE_READ;
    protected $lf               = "\n";
    protected $binary_safe      = true;
    public $fs                  = null;

    public function __construct($file = null, $mode = FMODE_READ) {
        parent::__construct();
        $this->setMode($mode);
        if (!is_null($file)) {
            $this->setFile($file);
        }
    }

    private function check_handle_open() {
        if (is_resource($this->handle)) {
            throw (new \core\exceptions\IllegalStateException("Could not delete file '".$this->file."': File still open."));
        }
    }

    private function check_file_exists($message) {
        if (!$this->exists()) {
            throw (new \core\exceptions\FileNotFoundException($message.": File not found."));
        }
    }

    public function setFile($file = null) {
        if (!is_null($file)) {
            $this->file = $file;
        }

        if (!is_null($this->file)) {
            $this->fs = new \core\filesystem\FS($this->file);
            ($this->exists())   ?   $this->open()   : $this->create();
        }
    }

    public function setMode($mode = FMODE_READ) {
        $this->mode = $mode;
    }

    public function setBinarySafe($safe = true) {
        $this->binary_safe = $safe;
    }

    public function open($file = null, $mode = null) {
        if (!is_null($file)) $this->file = $file;

        # set binary safe mode flag
        if (is_null($mode)) {
            $mode = ($this->binary_safe) ? $this->mode."b" : $this->mode;
        }

        trace("opening file ".$this->file." with mode ".$mode, $this);

        if (false === $this->handle = fopen($this->file, $mode)) {
            throw (new \core\exceptions\OperationNotPermittedException("Cannot open file: '".$this->file."': Operation not permitted"));
        }   
    } 

    public function create($file = null) {
        $this->open($file, FMODE_WRITE);
    }

    public function exists() {
        return file_exists($this->file);
    }

    public function write($string) {
        if (!$this->exists()) {
            trace ("file ".$this->file." does not exist, creating", $this);
            $this->create();
        }

        if (!is_resource($this->handle)) {
            $this->open();
        }

        trace("Writing to file", $this);
        
        if (false === fputs($this->handle, $string.$this->lf, strlen($string.$this->lf))) {
            throw new \core\exceptions\OperationNotPermittedException("Could not write data to file");
        }
        return true;
    }

    public function close() {
        if (is_resource($this->handle)) {
            trace("closing file ".$this->file, $this);
            fclose($this->handle);
        }
    }

    public function eof() {
        return feof($this->handle);
    }

    public function next() {
        if (!$this->eof()) {
            return $this->readLine();
        }
    }

    public function get($file = null) {
        if (!is_null($file)) {
            $this->file = $file;
            $this->open();
        }
        
        $contents = "";

        while ($content = $this->read()) {
            $contents .= $content;
        }
        return $contents;
    }

    public function read($bytes = 4096) {
        if (0 === $bytes) return "";

        if (false === ($result = fread($this->handle, $bytes)) && (!$this->eof($this->handle)) ) {
            throw (new \core\exceptions\IOException("Cannot read $bytes bytes from file '".$this->file."'"));
        }

        return $result;
    }

    public function readLine($bytes = 4096) {
        if (0 === $bytes) return "";

        if (false === ($result = fgets($this->handle, $bytes)) && (!$this->eof($this->handle)) ) {
            throw (new \core\exceptions\IOException("Cannot readline $bytes bytes from file '".$this->file."'"));
        }

        return chop($result);
    }

    public function delete() {
        if (!$this->exists()) return true;

        if (is_resource($this->handle)) {
            $this->close();
        }

        trace("deleting file ".$this->file, $this);

        if (false === unlink($this->file)) {
            throw (new \core\exceptions\IOException("Could not delete file '".$this->file."'"));
        }

        return true;
    }

}

?>
