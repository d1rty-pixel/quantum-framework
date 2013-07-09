<?php

uses ("addon.Addon");

class EmailAddon extends Addon {

    public function __construct($alias, $prototype = null, $datastore = null) {
        parent::__construct("email", $alias, $prototype, $datastore);
    }

    public function dispatch() {
    }

}

?>
