<?php

uses ("core.base.Quobject");
uses ("core.base.QSocket");
uses ("core.data.rss.RSSImage", "core.data.rss.RSSStory");
uses ("core.filesystem.File");

class RSSFeed extends Quobject {

    var $title;
    var $copyright;
    var $description;
    var $image;
    var $stories;
    var $url;
    var $xml;
    var $link;
    var $error;
    var $maxstories;

    function __construct($uri='') {
        parent::__construct();
        $this->reset();
        $this->image = new RSSImage();
    }

    private function reset() {
        $this->title        = "";
        $this->copyright    = "";
        $this->description  = "";
        $this->image        = null;
        $this->stories      = array();
        $this->url          = "";
        $this->xml          = "";
        $this->link         = "";
        $this->error        = "";
        $this->maxstories   = 0;
    }

    public function setMax($max) {
        $this->maxstories = $max;
    }

    public function parseFromString($string) {
        $this->xml = $string;

        $parser = xml_parser_create();
        global $rss;
        $rss = $this;
        xml_set_element_handler($parser, "startElement", "endElement");
        xml_set_character_data_handler($parser, "characterData");
        $retval = @xml_parse($parser, $this->xml, true);

        if (!$retval) {
            $this->error = sprintf("XML error while parsing '".$uri."'\n: %s at line %d",
                xml_error_string(xml_get_error_code($parser)),
                xml_get_current_line_number($parser));
        }
        xml_parser_free($parser);
    }

    public function parse($uri) {
        if (preg_match("/^https?:\/\//", $uri)) {
            $this->url = $uri;
            if (extension_loaded("curl")) {
                $this->xml = $this->getRemoteFile($this->url);
            } else {
                throw new ClassNotFoundException("CURL is needed");
            }  
        } elseif (strlen($uri) != 0) {
            $this->xml = $this->getLocalFile($uri);
        } else {
            throw new IOException("No file or URL for RSS feed");
        }

        $this->parseFromString($this->xml);        
    }
   
    private function getLocalFile($file) {
        $file = new File($file);
        return $file->get();
    }
 
    private function getRemoteFile($url) {
        $s = new QSocket();
        if ($s->getUrl($url)){
            if (is_array($s->headers)) {
                $h = array_change_key_case($s->headers, CASE_LOWER);
                if ($s->error) // failed to connect with host
                    $buffer = $this->errorReturn($s->error);
                elseif (preg_match("/404/",$h['status'])) // page not found
                    $buffer = $this->errorReturn("Page Not Found");
                elseif (preg_match("/xml/i",$h['content-type'])) // got XML back
                    $buffer = $s->page;
                else // got a page, but wrong content type
                    $buffer = $this->errorReturn("The server ($url) did not return XML. The content type returned was ".$h['content-type']);
            } else {
                $buffer = $this->errorReturn("An unknown error occurred.");
            }
        } else {
            $buffer = $this->errorReturn("An unknown error occurred.");
        }
        return $buffer;
    }
  
    private function errorReturn($error){
        $retVal="<?xml version=\"1.0\" ?>\n".
            "<rss version=\"2.0\">\n".
            "\t<channel>\n".
            "\t\t<title>Failed to Get RSS Data</title>\n".
            "\t\t<description>An error was ecnountered attempting to get the RSS data: $error</description>\n".
            "\t\t<pubdate>".date("D, d F Y H:i:s T")."</pubdate>\n".
            "\t\t<lastbuilddate>".date("D, d F Y H:i:s T")."</lastbuilddate>\n".
            "\t</channel>\n".
            "</rss>\n";
        return $retVal;
    }

    public function addStory($o){
        if(is_object($o))
            $this->stories[]=$o;
        else
            $this->error="Type mismatach: expected object";
    }


}

function startElement($parser, $name, $attrs) {
    global $insideitem, $tag, $isimage;
    $tag = $name;
    if($name=="IMAGE")
        $isimage=true;
    if ($name == "ITEM") {
        $insideitem = true;
    }
}


function endElement($parser, $name) {
    global $insideitem, $title, $description, $link, $pubdate, $stories, $rss, $globaldata, $isimage;
    $globaldata=trim($globaldata);
    // if we're finishing a news item
    if ($name == "ITEM") {
        // create a new news story object
        $story=new RSSStory();
        // assign the title, link, description and publication date
        $story->title=trim($title);
        $story->link=trim($link);
        $story->description=trim($description);
        $story->pubdate=trim($pubdate);
        // add it to our array of stories
        $rss->addStory($story);
        // reset our global variables
        $title = "";
        $description = "";
        $link = "";
        $pubdate = "";
        $insideitem = false;
    } else {
        switch($name){
            case "TITLE":
                if(!$isimage)
                    if(!$insideitem)
                        $rss->title=$globaldata;
                break;
            case "LINK":
                if(!$insideitem)
                    $rss->link=$globaldata;
                break;
            case "COPYRIGHT":
                if(!$insideitem)
                    $rss->copyright=$globaldata;
                break;
            case "DESCRIPTION":
                if(!$insideitem)
                    $rss->description=$globaldata;
                break;
        }
    }
    if($isimage){
        switch($name){
            case "TITLE": $rss->image->title=$globaldata;break;
            case "URL": $rss->image->url=$globaldata;break;
            case "LINK": $rss->image->link=$globaldata;break;
            case "WIDTH": $rss->image->width=$globaldata;break;
            case "HEIGHT": $rss->image->height=$globaldata;break;
        }
    }
    if($name=="IMAGE")
        $isimage=false;
    $globaldata="";
}

function characterData($parser, $data) {
    global $insideitem, $tag, $title, $description, $link, $pubdate, $globaldata;
    if ($insideitem) {
        switch ($tag) {
            case "TITLE":
                $title .= $data;
                break;
            case "DESCRIPTION":
                $description .= $data;
                break;
            case "LINK":
                $link .= $data;
                break;
            case "PUBDATE":
            case "DC:DATE":
                $pubdate .= $data;
                break;
        }
    } else {
        $globaldata.=$data;
    }
}

?>
