<?php

namespace core\base;

uses ("core.base.Quobject");

class QSocket extends \core\base\Quobject {
    private $name="QSocket";
    private $version="0.1";
    private $userAgent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
    public $headers;   
    public $page="";
    public $result="";
    public $redirects=0;
    public $maxRedirects=3;
    public $error="";  

    function getUrl($url) {
        $retVal=""; 
        $url_parsed = parse_url($url);
        $scheme     = $url_parsed["scheme"];
        $host       = $url_parsed["host"];
        $port       = (!empty($url_parsed["port"]))     ? $url_parsed["port"]       : "80";
        $user       = (!empty($url_parsed["user"]))     ? $url_parsed["user"]       : "";
        $pass       = (!empty($url_parsed["pass"]))     ? $url_parsed["pass"]       : "";
        $path       = (!empty($url_parsed["path"]))     ? $url_parsed["path"]       : "/";
        $query      = (!empty($url_parsed["query"]))    ? $url_parsed["query"]      : "";
        $anchor     = (!empty($url_parsed["fragment"])) ? $url_parsed["fragment"]   : "";

        if (!empty($host)){

            // attempt to open the socket
            if($fp = fsockopen($host, $port, $errno, $errstr, 2)){

                $path .= $query?"?$query":"";
                $path .= $anchor?"$anchor":"";

                // this is the request we send to the host
                $out = "GET $path ".
                    "HTTP/1.0\r\n".
                    "Host: $host\r\n".
                    "Connection: Close\r\n".
                    "User-Agent: $this->userAgent\r\n";
                if($user)
                    $out .= "Authorization: Basic ".base64_encode("$user:$pass")."\r\n";
                $out .= "\r\n";

                fputs($fp, $out);
                while (!feof($fp)) {
                    $retVal.=fgets($fp, 128);
                }
                fclose($fp);
            } else {
                $this->error="Failed to make connection to host.";//$errstr;
            }
            $this->result=$retVal;
            $this->headers=$this->parseHeaders(trim(substr($retVal,0,strpos($retVal,"\r\n\r\n"))));
            $this->page=trim(stristr($retVal,"\r\n\r\n"))."\n";
            if(isset($this->headers['Location'])){
                $this->redirects++;
                if($this->redirects<$this->maxRedirects){
                    $location=$this->headers['Location'];
                    $this->headers=array();
                    $this->result="";
                    $this->page="";
                    $this->getUrl($location);
                }
            }
        }
        return (!$retVal="");
    }


    private function parseHeaders($s){
        $h=preg_split("/[\r\n]/",$s);
        foreach($h as $i){
            $i=trim($i);
            if(strstr($i,":")){
                list($k,$v)=explode(":",$i);
                $hdr[$k]=substr(stristr($i,":"),2);
            }else{
                if(strlen($i)>3)
                    $hdr[]=$i;
            }
        }
        if(isset($hdr[0])){
            $hdr['Status']=$hdr[0];
            unset($hdr[0]);
        }
        return $hdr;
    }

}
