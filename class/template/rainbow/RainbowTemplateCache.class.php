<?php

namespace core\template\rainbow;

uses (
    "core.base.Quobject",
    "core.filesystem.FileSystem"
);

class RainbowTemplateCache extends \core\base\Quobject {

    private $expire_time        = 3600;

    private $cache_dir          = null;

    private $dir                = null;

    protected $supported_vars   = array("expire_time", "cache_dir");


    public function __construct() {
        $this->cache_dir = sprintf("%s/_cache", APP_ROOT);
        parent::__construct();

        $this->dir = new \core\filesystem\FileSystem($this->cache_dir, "Directory");

        if (!$this->dir->exists()) {
            $this->dir->create();
        }

    }

    public function __set($key, $value) {
        if (!in_array($key, $this->supported_vars)) return false;
        if ($key == "enabled") {
            $this->$key = ($value == "true" || $value == "1") ? true : false;
        } else {
            $this->$key = $value;
        }
    }

    public function isExpired($template_name = null) {
        if (is_null($template_name)) return true;

        
    }

    public function getCacheDir() {
        return $this->cache_dir;
    }


    public function exists() {

    }


    public function generateCacheFilename($data) {
        return sprintf("%s/%s.rtpl", $this->cache_dir, md5($data.implode(\Request::getSimilar())));
    }

    public function write($filename, $content) {
#        try {
            $file = new \core\filesystem\File($filename);
            $file->write($content);
            $file->close();
#        } catch (\Exception $e) {
#            throw new 
#        }
    }

    public function clean() {
        $files = glob(sprintf("%s/*.rtpl.php", $this->cache_dir));
        $time = time() - $this->expire_time;
        foreach ($files as $file)
            if ($time > filemtime($file) )
                unlink($file);
    }

}

?>
