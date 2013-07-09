<?php

/**
 * Class HighchartController
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <tristan.cebulla@quantum-framework.org>
 * @copyright 2005 - 2011
 * @package addons.highchart.class
 */

uses ("core.base.mvc.Controller", "addon.highchart.class.HighchartJson", "addon.highchart.class.HighchartFormat");

class HighchartController extends Controller {

    private $config                         = null;    
    private $chart_format_tooltip_suffix    = null;
    private $chart_format_override          = null;
    
    public function init() {
        $this->config               = new StdClass();
        $this->config->height       = "400px";
        $this->config->width        = "100%";
        $this->config->renderto     = "highchart_container";
        $this->config->class        = "form";
        $this->config->title        = "Default Chart";
        $this->config->model_method = "get";
        $this->config->options      = array();        
    }

    private function getDefaultPrototype() {
        $format_class = "HighchartFormat";
        if (!is_null($this->chart_format_override)) {
            uses ($this->chart_format_override);
            $format_class = $this->getLastNode($this->chart_format_override);
        }
    
        return array(
            "chart"     => array(
                "renderTo"  => $this->config->renderto,
                "zoomType"  => "x",
                "resetZoomButton"   => array(
                    "position"          => array(
                        "x" => 0,
                        "y" => -30,
                    ),
                ),
            ),
            "title"     => array(
                "text"      => $this->config->title,
            ),
            "tooltip"   => array(
                "formatter" => new HighchartJson($format_class::getTooltipFormat($this->chart_format_tooltip_suffix)),
            ),
            "xAxis"     => array(
                "type"      => "datetime",
                "maxZoom"   => 3600 * 1000,    
            ),
            "series"    => array(),
            "credits"   => array(
                "enabled"   => false,
            ),
            "navigation"    => array(
                "buttonOptions" => array(
                    "enabled"       => true,
                    "borderRadius"  => 5,
                ),
            ),
        );        
    }

    private function overwriteConfig($prototype, $key1, $key2 = null) {
        if (is_null($key2)) $key2 = $this->config->$key1;
        if (!is_null($prototype[$key1])) {
            $this->config->$key2 = $prototype[$key1];
            unset($prototype[$key1]);
        }
        return $prototype;
    }

    public function configure($prototype = null) {
    
        // no prototype given, take default prototype from model
        if (is_null($prototype)) {
            $this->config->options = $this->getDefaultPrototype();
        // array or object prototypes
        } else if ( (is_array($prototype)) || (is_object($prototype)) ) {
            // first, cast $prototype to an array
            $prototype = (Array) $prototype;
 
            // then overwrite all data from $prototype to $this->config
            foreach (array("height", "width", "renderto", "class", "title") as $name) {
                $prototype = $this->overwriteConfig($prototype, "chart_".$name, $name);
            }

            // handle special vars, dont forget to delete them 
            if (!is_null($prototype["chart_format_tooltip_suffix"])) {
                $this->chart_format_tooltip_suffix = $prototype["chart_format_tooltip_suffix"];
                unset($prototype["chart_format_tooltip_suffix"]);
            }
            
            if (!is_null($prototype["chart_format_override"])) {
                $this->chart_format_override = $prototype["chart_format_override"];
                unset($prototype["chart_format_override"]);
            }
             
            if (!is_null($prototype["model_method"])) {
                $this->config->model_method = $prototype["model_method"];
                unset($prototype["model_method"]);
            }

            // merge default array with $prototype and assign to $this->config->options
            $this->config->options = array_merge_recursive($this->getDefaultPrototype(), (Array) $prototype);
        // pure, (php-compatible) native json - data series must come from there!
        } else {
            $this->config->options = $prototype;
        }
        
        if (is_array($this->config->options)) {
            $method = $this->config->model_method;
            trace("Calling model method $method", $this);
            $this->config->options["series"] = $this->model->$method();
        }
    }
    
    public function onGet() {
        $this->view->display($this->config);
    }

    public function onPost() {
        $this->onGet();
    }

}

?>
