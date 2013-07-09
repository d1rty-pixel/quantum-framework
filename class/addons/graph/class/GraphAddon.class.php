<?php

uses ("addon.Addon");

class GraphAddon extends Addon {

    protected $hooks = array(
        "bla" => "fasel",
    );

    protected $save_path        = null;

    protected $_prototype = array();


    private function setPrototype() {
    }

    public function dispatch() {
        debug ("Running MVC-Controller for addon '".$this->name."' as '".$this->alias."' with datastore '".$this->datastore["name"]."'", $this);
        $this->run();
#        var_dump($this->controller); ### hier das model laden und getPrototype() ausfÃhren und damit this->controller->configure fÃttern .. spÃter wird im controller das model nach den daten gefragt und mit den methoden von alt:graphcontroller (nach neu:addon.graph. also hier .. GraphAddon) fuettern

        $model = $this->controller->getModelInstance();

        $this->controller->configure($this->setPrototype());
        $this->controller->onGet();

        ## datastore inhalt holen, muss halt ein spezifisches format haben.
        /*
    z.b.

    "config"    => config aus konstruktor,
    "plots" => array(
        "ident" => array(
            type => "single",
            axis => y|y2,
            plots => plots,

        ),
        
    ),

        */
    }

}

?>
