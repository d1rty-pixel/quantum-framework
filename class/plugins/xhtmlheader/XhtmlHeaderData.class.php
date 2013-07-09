<?php

namespace core\plugins\xhtmlheader;

uses (
    "core.base.Quobject"
);

class XhtmlHeaderData extends \core\base\Quobject {

    protected $title            = "Default Site Title";

    protected $links            = array();

    protected $scripts          = array();

    protected $supported_vars   = array("title", "links", "scripts");

    public function get() {
        foreach ($this->links as $id => $object) {
            $this->links[$id] = (array) $object;
        }
        foreach ($this->scripts as $id => $object) {
            $this->scripts[$id] = (array) $object;
        }

        return array(
            "title"     => $this->title,
            "links"     => (array) $this->links,
            "scripts"   => (array) $this->scripts,
        );
    }


}

?>
