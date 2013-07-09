<?php

uses ("addon.gallery.class.Addon");
uses ("addon.gallery.class.GalleryController", "addon.gallery.class.GalleryModel", "addon.gallery.class.GalleryView");

class GalleryAddon extends Addon {

    public function configure() {
        $this->controller = new GalleryGraphController();
    }

}

?>
