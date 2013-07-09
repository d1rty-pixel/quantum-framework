<?php

namespace core\base;

uses ("core.base.Quobject");

abstract class RequestBase extends \core\base\Quobject {

    protected static $args_prefix = "request";

    public function __construct() {
        parent::__construct();
        $this->importRequestVars();
    }

   /**
    * __clone
    *
    * defend cloning
    */     
    private function __clone() {
    }

    protected function cleanInput($data){
        if (is_array($data)){
            foreach($data as $k => $v){
                $clean_input[$k] = $this->cleanInput($v);
            }
        } else {
            if (get_magic_quotes_gpc()){
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    }

    abstract protected function importRequestVars();

    static public function isGet($key) {
        return false;
    }

    static public function isPost($key) {
        return false;
    }

    /**
     * getArgument
     * @static
     *
     * @param String $arg key to return
     */
    public static function getArgument($arg) {
        return \core\Quantum::registry(sprintf("%s.%s", self::$args_prefix, $arg));
    }

    static public function isEmpty($arg) {
        $val = self::getArgument($arg);
        return empty($val);
    }


    /** 
     * implant()
     * @static
     *
     * @param String $key key 
     * @param String $value corresponding value
     *
     * implant/add a key->value pair to the args_ registry
     */
    static public function implant($arg, $value, $type = "get") {
        \core\Quantum::registry(sprintf("%s.%s", self::$args_prefix, $arg), $value);
        if (strtolower($type) == "get") {
            $_GET[$arg] = $value;
        } else if (strtolower($type) == "post") {
            $_POST[$arg] = $value;
        }
    }

    static public function check() { 
        $no_error = true; 
        $args = func_get_args(); 
   
        for ($i=0; $i < count($args); $i++) { 
            $value = Request::getArgument($args[$i]); 
            if (empty($value)) { $no_error = false; break; } 
        } 
        return $no_error; 
    } 
   
 
    static public function getSimilar($prefix = null) { 
        $hits = array();

        if (is_null($prefix)) $prefix = self::$args_prefix;

        $regex = sprintf("/^%s./", $prefix);
        foreach (\core\Quantum::registry() as $key => $value) { 
            if (preg_match($regex, $key)) { 
                $hits[$key] = $value; 
            } 
        } 
        return $hits; 
    }

}

?>
