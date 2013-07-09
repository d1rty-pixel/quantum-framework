<?php

namespace core\application;

uses(
    "core.base.Quobject",
    "core.exceptions.ScriptletException"
);

class ContentType extends \core\base\Quobject {

    protected $content_type           = null;

    private $default_content_type   = "text/plaintext";

    static $TYPE_MAPPING            = array(
        "text/plaintext"        => "plaintext",
        "text/html"             => "xhtml",
        "text/json"             => "json",
        "application/json"      => "json",
    );

    protected $supported_vars   = array("content_type");

    public function __construct() {

        $headers = $this->get_headers();
        if ($headers === FALSE) {
            $this->content_type = $this->default_content_type;
        } else {
            $this->content_type = $this->determineContentType($headers);
        }

        parent::__construct();

        debug(sprintf("Determined content-type '%s'", $this->content_type), $this);

        # ist ja eigentlich gar kein scriptlet -> umbenennen
        $content_type_scriptlet = $this->getContentTypeScriptlet();
        if (is_null($content_type_scriptlet) || empty($content_type_scriptlet)) {
            throw new \core\exceptions\ScriptletException(sprintf("Did not find a suitable content scriptlet for content type '%s'", $this->content_type));
        }

        debug(sprintf("Content-type '%s' is represented by %sScriptlet", $this->content_type, ucfirst($content_type_scriptlet)), $this);
    }

    private function get_headers() {
        if (!function_exists("getallheaders")) {
            return false;
        }
        return (Object) getallheaders();
    }

    private function determineContentType($headers) {
        if (!isset($headers->Accept)) {
            return $this->default_content_type;
        } 

        $content_types = explode(',', $headers->Accept);
        if (count($content_types) == 0) {
            return $this->default_content_type;
        }

        return $content_types[0];
    }
    
    public function getContentType() {
        return $this->content_type;
    }

    public function getContentTypeScriptlet() {
        return self::$TYPE_MAPPING[$this->content_type];
    }

}

?>
