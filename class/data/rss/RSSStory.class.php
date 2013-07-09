<?php

uses ("core.base.Quobject");

class RSSStory extends Quobject {
    public $title = "";
    public $link = "";
    public $description = "";
    public $pubdate = "";

    function show(){
        $string = "";
        if($this->title){
            if($this->link){
                $string .= "<dt><a href=\"$this->link\">$this->title</a></dt>\n";
            }elseif($this->title){
                $string .= "<dt>$this->title</a></dt>\n";
            }
            $string .= "<dd>";
            if($this->pubdate)
                $string .= "<i>$this->pubdate</i> - ";
            if($this->description)
                $string .= "$this->description";
            $string .= "</dd>\n";
        }
    }
}

?>
