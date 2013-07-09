<?php

namespace core\filesystem;

uses("core.base.Quobject");

class FS extends \core\base\Quobject {

    private $fs;

    public function __construct($fs) {
        $this->fs = $fs;
    }

    public function atime() {
        return fileatime($this->fs);
    }

    public function ctime() {
        return filectime($this->fs);
    }

    public function mtime() {
        return filemtime($this->fs);
    }

    public function node() {
        return fileinode($this->fs);
    }

    public function owner() {
        return fileowner($this->fs);
    }

    public function group() {
        return filegroup($this->fs);
    }

    public function chmod($mode) {
        return @chmod($this->fs, $mode);
    }

    public function chown($user) {
        return chown($this->fs, $user);
    }

    public function chgrp($group) {
        return chgrp($this->fs, $group);
    }

    public function type() {
        return filetype($this->fs);
    }

    public function size() {
        return filesize($this->fs);
    }

    public function free_space() {
        return disk_free_space($this->fs);
    }

    public function total_space() {
        return $disk_total_space($this->fs);
    }

}

?>
