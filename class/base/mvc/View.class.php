<?php

namespace core\base\mvc;

uses(
    "core.base.Quobject",
    "core.template.rainbow.RainbowDocument",
    "core.template.rainbow.RainbowTemplate"
);

/**
 * Class View
 *
 * This is part of the MVC concept design here used by the Quantum Framework.
 * The View does the rest 
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base.mvc
 */
class View extends \core\base\Quobject {

	protected $document;
    protected $model;

	public function __construct($model) {
        $this->model = $model;
		$this->document = \core\base\Singleton::getInstance('\core\template\rainbow\RainbowDocument');
        parent::__construct();
	}

    public function getTemplate() {
        return new \core\template\rainbow\RainbowTemplate();
    }  

}

?>
