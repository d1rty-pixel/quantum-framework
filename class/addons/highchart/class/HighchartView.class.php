<?php

uses ("core.base.mvc.View");

class HighchartView extends View {

    public function display($config) {
        $this->template->addAddonTemplate("graph", "highchart");  
        
        // $config["options"] may be an array, which is ok since it will get 
        // parsed by xjson_encode().
        // it also may contain native json (php compatible) - 
        // note that this string must contain *all neccessary* options for the 
        // highchart javascript class   
        if (is_array($config->options)) $config->options = xjson_encode($config->options);
        
        $this->template->setVar("graph", "container", array(
            "options"   => $config->options,
            "class"     => $config->class,
            "renderto"  => $config->renderto,
            "title"     => $config->title,
            "width"     => $config->width,
            "height"    => $config->height,
        ));
        
        $this->template->parse("graph");
    }    

}

?>
