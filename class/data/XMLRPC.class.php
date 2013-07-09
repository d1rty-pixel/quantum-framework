<?php

class XMLRPC extends Quobject {

    private $rpc_server;
    private $rpc_method;
    private $rpc_data;
    private $rpc_alias;
    
    public function setServer($server) {
        $this->rpc_server = $server;
    }

    public function setMethod($method) {
        $this->rpc_method = $method;
    }

    public function setAlias($alias) {
        $this->rpc_alias = $alias;
    }

    public function setMethodData($array) {
        $this->rpc_data = $array;
    }

    public function send_query() {
        $args = array(
            "alias" => $this->rpc_alias,
            "args"  => $this->rpc_data,
        );

        trace("Contacting RPC Server '".$this->rpc_server."' with method '".$this->rpc_method."'", $this);

        $request = xmlrpc_encode_request($this->rpc_method, $args);
        $context = stream_context_create(array(
            "http"  => array(
                "method"    => "POST",
                "header"    => "Content-type: text/xml",
                "content"   => $request
        )));

        $file = file_get_contents($this->rpc_server, false, $context);
        $response = xmlrpc_decode($file);

        if ($response && @xmlrpc_is_fault($response)) {
            error("xml_rpc: $response[faultString] ($response[faultCode])", $this);
            return $response;
            return null;
        } else {
            return $response;
        }
    }

}

?>
