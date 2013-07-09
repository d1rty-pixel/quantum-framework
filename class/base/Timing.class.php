<?php

namespace core\base;

uses ("core.base.Quobject");

class Timing extends \core\base\Quobject {

	private $start  = 0.0;
	private $stop   = 0.0;

	public function __construct($start = true) {
		if ($start) {
			$this->start();
		}
		#parent::__construct(); # evil evil evil
	}

	private function reset() {
		$this->start    = 0.0;
		$this->stop     = 0.0;
	}

    public function start() {
        $this->reset();
        $this->start = microtime(true);
    }

    public function stop() {
        $this->stop = microtime(true);
    }

    public function elapsed() {
        $this->stop();
        return $this->stop - $this->start;
    }

}

?>
