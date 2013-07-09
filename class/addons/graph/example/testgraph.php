<?php

uses ("addon.graph.class.GraphController", "addon.graph.class.GraphModel", "addon.graph.class.GraphView");

class TestGraphController extends GraphController {

    public function init() {
        $config_array = array(
            "height"            => 800,
            "width"             => 400,
            "title"             => "TestGraph",
            "y_scale"           => "textint",
            "x_title"           => "days",
            "y_title"           => "values",
            "x_labels"          => array(
                "day1", "day2", "day3",
            ),
            "legend_layout"     => LEGEND_HOR,
            "x_color"           => "gray",
            "y_color"           => "gray",
        );

        $this->configure($config_array);
        $this->addLine("y", "line0", array(2,5,7), array("legend" => "line 0"));
        $this->addLine("y", "line1", array(7,2,5), array("legend" => "line 1"));
    }

}

class TestGraphModel extends GraphModel {

}

class TestGraphView extends GraphView {

}

$controller = new GraphController($config_array);

?>
