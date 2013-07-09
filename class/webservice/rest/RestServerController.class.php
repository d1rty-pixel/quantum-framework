<?php

namespace core\webservice\rest;

uses ("core.base.mvc.Controller", "core.filesystem.File", "core.exceptions.RestException");

abstract class RestServerController extends \core\base\mvc\Controller {

    protected $request_method   = null;
    protected $controller       = null;
    protected $module           = null;
    protected $headers          = null;
    protected $request          = null;
    protected $method           = "get";

    protected $status_responses = array(
        100 => 'Continue',  
        101 => 'Switching Protocols',  
        200 => 'OK',
        201 => 'Created',  
        202 => 'Accepted',  
        203 => 'Non-Authoritative Information',  
        204 => 'No Content',  
        205 => 'Reset Content',  
        206 => 'Partial Content',  
        300 => 'Multiple Choices',  
        301 => 'Moved Permanently',  
        302 => 'Found',  
        303 => 'See Other',  
        304 => 'Not Modified',  
        305 => 'Use Proxy',  
        306 => '(Unused)',  
        307 => 'Temporary Redirect',  
        400 => 'Bad Request',  
        401 => 'Unauthorized',  
        402 => 'Payment Required',  
        403 => 'Forbidden',  
        404 => 'Not Found',  
        405 => 'Method Not Allowed',  
        406 => 'Not Acceptable',  
        407 => 'Proxy Authentication Required',  
        408 => 'Request Timeout',  
        409 => 'Conflict',  
        410 => 'Gone',  
        411 => 'Length Required',  
        412 => 'Precondition Failed',  
        413 => 'Request Entity Too Large',  
        414 => 'Request-URI Too Long',  
        415 => 'Unsupported Media Type',  
        416 => 'Requested Range Not Satisfiable',  
        417 => 'Expectation Failed',  
        500 => 'Internal Server Error',  
        501 => 'Not Implemented',  
        502 => 'Bad Gateway',  
        503 => 'Service Unavailable',  
        504 => 'Gateway Timeout',  
        505 => 'HTTP Version Not Supported'
    );

    public function init() {
        $this->request_method   = \Request::getRequestMethod();
        $this->controller       = \Request::getArgument("qf_controller");
        $this->module           = \Request::getArgument("qf_module");
        $this->headers          = (Object) apache_request_headers();

        switch ($this->request_method) {
            case "POST":
                # hier muss ich noch viel machen -> unterscheidung string, json/uri-parameter, anything else
                $file = new \core\filesystem\File("php://input"); $raw = $file->read();
                $this->request = $this->cleanInput($raw);
                $this->method = "post"; # something like add
                break;
            case "GET":
                $this->request = $this->cleanInput($_GET);
                $this->method = "get";
                break;
            case "DELETE":
                $file = new \core\filesystem\File("php://input"); $raw = $file->read();
                $this->request = $this->cleanInput($raw); 
                $this->method = "delete";
                break;
            case "PUT":
                $file = new \core\filesystem\File("php://input"); $raw = $file->read();
                $this->request = $this->cleanInput($raw);
                $this->method = "put";  # like update
                break;
            case "OPTIONS":
                $file = new \core\filesystem\File("php://input"); $raw = $file->read();
                $this->request = json_decode($raw);
                $this->method = "options";  # like update
            default:
                $this->request = $this->cleanInput($_REQUEST);
                break;
        }

        foreach ($this->request as $key => $value) {
            \Request::implant($key, $value);
        }


        $this->request = (Object) $this->request;


        if (isset($this->headers->{'Access-Control-Request-Method'})) {
            $this->method = strtolower($this->headers->{'Access-Control-Request-Method'});
        }

        # override method
        if (isset($this->request->_method)) {
            trace(sprintf("Override method '%s'", $this->request->_method), $this);
            $this->method = $this->request->_method;
        }

        if (!method_exists($this->model, $this->method)) {
            throw new \core\exceptions\RestException(sprintf("Method %s does not exist with Model %s", $this->method, $this->model->getClassName()));
        }
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

    abstract public function onPut();

    abstract public function onDelete();

    public function perform($format) {
        $result = null;
        try {
            $result = $this->model->{$this->method}();
            $this->view->display($result, $format);
        } catch (\core\exceptions\GuruMeditationException $e) {
            header(sprintf("HTTP/1.1 %s %s", 400, $this->status_responses[400]));
            $this->view->display(array("error" => $e->getMessage()));
        } 
        
    }

}

?>
