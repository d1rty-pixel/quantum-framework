<?php

require_once("jpgraph/jpgraph.php");
require_once("jpgraph/jpgraph_line.php");
require_once("jpgraph/jpgraph_bar.php");
require_once("jpgraph/jpgraph_date.php");

uses ("core.base.mvc.Controller");
uses ("core.base.Singleton", "core.config.Config", "core.filesystem.FileSystem");
uses ("core.exceptions.IllegalArgumentException");

class GraphController extends Controller {

    private $save_path			= null;
    private $graph      		= null;
    private $plots      		= array();
    private $plot_correlation   = array();
	private $output_type		= null;
	private $template_name      = "graph.template.display";
	private $image_file			= null;

/*
example call:

$class = mygraphcontrollerclass(array(
    "height"            => 800,
    "width"             => 400,
    "title"             => "title",
    "y_scale"           => "textint",
    "y2_scale"          => "lin",
    "y2_scale_min"      => 0,
    "y2_scale_max"      => 105,
    "x_title"           => "time",
    "y_title"           => "left y-axis",
    "y2_title"          => "right y(2)-axis",
    "x_labels"          => array(
        "day1", "day2", "day3",
    ),
    "legend_layout"     => LEGEND_HOR,
    "x_color"           => "gray",
    "y_color"           => "gray",
    "y2_color"          => "gray",    
));

*/

    public function init() {
        # fla
    }

    public function configure($config) {
        var_dump($config);
        foreach (array("height", "width", "y_scale") as $name) {
#            if (empty($config[$name])) 
#                throw New IllegalArgumentException("Not enough parameters (missing $name)");
        }

		# set save path
        $this->save_path = APP_ROOT."/tmp/";

		# set output type
		$this->output_type = (empty($config["output_type"])) ? "browser" : "file";

		# set output template name
		$this->template = (empty($config["template"])) ? "graph.template.display" : $config["template"];

	
		# initialize graph with width and height
        $this->graph = new Graph($config["width"], $config["height"]);

        # set graph margin
        (empty($config["margin"])) ? $this->graph->setMargin(40,40,30,30) : $this->graph->setMargin($config["margin"]);

        # set title
        (empty($config["title"])) ? $this->setTitle("Unnamed Graph") :$this->setTitle($config["title"]);

        # set Y axis scale
        $this->setScale("y",
            $config["y_scale"],
            empty($config["y_scale_min"]) ? null : $config["y_scale_min"],
            empty($config["y_scale_max"]) ? null : $config["y_scale_max"]
        );
        
        # set Y2 axis scale (if set)
        if (!empty($config["y2_scale"])) {
            $this->setScale("y2",
                $config["y2_scale"],
                empty($config["y2_scale_min"]) ? null : $config["y2_scale_min"],
                empty($config["y2_scale_max"]) ? null : $config["y2_scale_max"]
            );
        }

        # set axis titles, X and Y are default
        $this->setAxisTitle("x", (empty($config["x_title"])) ? "X-Axis" : $config["x_title"]);
        $this->setAxisTitle("y", (empty($config["y_title"])) ? "Y-Axis" : $config["y_title"]);
        # set Y2 axis title (if set)
        if (!empty($config["y2_title"])) {
            $this->setAxisTitle("y2", $config["y2_title"]);
        }
       
        # set X axis labels (ticks)
        if (!empty($config["x_labels"])) {
            $this->setXLabels($config["x_labels"]);
        }

        # set axis colors (not the graph colors!)
        if (!empty($config["x_color"]))     { $this->setAxisColor("x",  $config["x_color"]); }
        if (!empty($config["y_color"]))     { $this->setAxisColor("y",  $config["y_color"]); }
        if (!empty($config["y2_color"]))    { $this->setAxisColor("y2", $config["y2_color"]); }

        # set legend layout
        (!empty($config["legend_layout"])) ? $this->setLegendLayout(LEGEND_HOR) : $this->setLegendLayout($config["legend_layout"]);

        $this->graph->xaxis->scale->ticks->Set(60*60*24);

    }

	public function setOutputFormat($format = "browser") {
		$this->output_type = $format;
	}

    public function setTitle($title) {
        $this->graph->title->Set($title);
    }

    public function setLegendLayout($layout) {
        $this->graph->legend->SetLayout($layout);
        $this->graph->legend->setFrameWeight(1);
    }

    public function setScale($axis, $name, $min = null, $max = null) {
        if ($axis == "y") {
            $this->graph->setScale($name, $min, $max);
        } elseif ($axis = "y2") {
            $this->graph->setY2Scale($name, $min, $max);
        }
    }

    public function setAxisTitle($axis, $title) {
        if ($axis == "x") {
            $this->graph->xaxis->title->Set($title);
        } elseif ($axis == "y") {
            $this->graph->yaxis->title->Set($title);
        } elseif ($axis == "y2") {
            $this->graph->y2axis->title->Set($title);
        }
    }

    public function setXLabels($labels) {
        $this->graph->xaxis->SetTickLabels($labels);
    }


    public function setAxisColor($axis, $color) {
        if ($axis == "x") {
            $this->graph->xaxis->setColor($color);
        } elseif ($axis == "y") {
            $this->graph->yaxis->setColor($color);
        } elseif ($axis == "y2") {
            $this->graph->y2axis->setColor($color);
        }
    }

    public function addLine($axis, $name, $plots, $config) {
        # set correlation
        if (!is_array($this->plot_correlation[$axis])) { $this->plot_correlation[$axis] = array(); }
        array_push($this->plot_correlation[$axis], $name);

        $this->plots[$name] = new LinePlot($plots);

        # plot config
        (empty($config["legend"])) ? $this->plots[$name]->setLegend("unknown line") : $this->plots[$name]->SetLegend($config["legend"]);
        if (!empty($config["color"]))   { $this->plots[$name]->setColor($config["color"]); }
        if (!empty($config["width"]))   { $this->plots[$name]->setWeight($config["width"]); }
        if ($config["set_center"])      { $this->plots[$name]->setBarCenter(true); }
    }

    public function addBar($axis, $name, $plots, $config) {
        # set correlation
        if (!is_array($this->plot_correlation[$axis])) { $this->plot_correlation[$axis] = array(); }
        array_push($this->plot_correlation[$axis], $name);

        $this->plots[$name] = new BarPlot($plots);

        # plot config
        (empty($config["legend"])) ? $this->plots[$name]->setLegend("unknown line") : $this->plots[$name]->SetLegend($config["legend"]);
        if (!empty($config["color"]))   { $this->plots[$name]->setFillColor($config["color"]); }
        if (!empty($config["width"]))   { $this->plots[$name]->setWeight($config["width"]); }
        if ($config["set_center"])      { $this->plots[$name]->setBarCenter(true); }
    }

	public function addGroupBar($group_name, $config) {
		$bars = array();
		foreach ($config as $bar_config) {
			$bar_name = $bar_config["name"];
			$bars[$bar_name] = $this->addBar($bar_config["axis"], $bar_config["name"], $bar_config["plots"], $bar_config["config"]);
		}
		$this->plots[$group_name] = new GroupBarPlot($bars);
	}

	private function correlate() {
        # data correlation
        foreach ($this->plot_correlation as $axis => $plotnames) {
            if ($axis == "y") {
                foreach ($plotnames as $plotname) {
                    $this->graph->Add($this->plots[$plotname]);
                }
            } else if ($axis == "y2") {
                foreach ($plotnames as $plotname) {
                    $this->graph->AddY2($this->plots[$plotname]);
                }
            }
        }
	}

	private function self_configure() {
        # auto-configuration of legend columns
        if (count($this->plots) >= 6) {
            $this->graph->legend->SetColumns(4);
        }
	}

	public function onGet() {
		$this->correlate();
		$this->self_configure();
		$stroke = false;

		if (count($this->plots) == 0) {
	        $quantum_config = Singleton::getInstance("Config");
            $this->image_file = $quantum_config->get("addon_graph_defaultimage");
		} else {
			$this->image_file = tempnam($this->save_path, "img_");
			$stroke = true;
		}

		if ($this->output_type == "file") {
			if ($stroke) { $this->graph->Stroke($this->image_file);
			$this->view->display($this->template_name, substr($this->image_file, strlen(APP_ROOT)));
		} elseif ($this->output_type == "browser") {
			if ($stroke) {
				$this->graph->Stroke();
			} else {
				$fs = new FileSystem();
				echo $fs->read($this->image_file);
			}
		} else {
			throw new IllegalArgumentException("Unsupported output type '".$this->output_type."'");
		}

    }
    }

	public function onPost() {
		$this->onGet();
	}

}

?>
