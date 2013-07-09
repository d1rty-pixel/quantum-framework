<?php

uses ("core.base.Quobject", "core.filesystem.FS");

class QDirectory extends Quobject {

    protected $handle;
    protected $directory;
    public $fs = null;

    public function __construct($directory = null) {
        parent::__construct();
        if (!is_null($directory)) {
            $this->setDirectory($directory);
        }
    }

    public function setDirectory($directory) {
        $this->directory = $directory;
        if (!$this->exists()) {
            throw (new DirectoryNotFoundException("Directory '".$this->directory."' not found"));
        }
        $this->fs = new FS($this->directory);
        $this->open();
    }

    public function create($mode = 0755, $recursive = true) {
        if (!mkdir($this->directory, $mode, $recursive)) {
            throw (new IOException("Could not create directory '".$this->directory."'"));
        }
    }

    public function open() {
        $this->handle = opendir($this->directory);
    }

    public function exists() {
        return file_exists($this->directory);
    }

    public function next() {
        $content = readdir($this->handle);
        if (!empty($content)) {
            return $content;
        }
        return false;
    }

    public function getAll() {
        return scandir($this->directory);
    }


}

?>
