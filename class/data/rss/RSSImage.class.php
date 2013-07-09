<?php

uses ("core.base.Quobject");

class RSSImage extends Quobject {
    public $title = "";
    public $url = "";
    public $link = "";
    public $width = 0;
    public $height = 0;

    function show($align=""){
        $string = "";

        if ($this->url) {
            if ($this->link)    $string .= "<a href=\"".$this->link."\">";

            $string .= "<img src=\"".$this->url."\" style=\"border:none;\"";

            if ($this->title)   $string .= " alt=\"".$this->title."\"";
            if ($this->width)   $string .= " width=\"".$this->width."\" height=\"".$this->height."\"";
            if ($align)         $string .= " align=\"$align\"";

            $string .= " target=\"_blank\">";
            if ($this->link)    $string .= "</a>";
        }
        return $string;
    }
}

