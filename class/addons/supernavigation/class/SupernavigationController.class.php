<?php

uses ("core.base.mvc.Controller");

class SupernavigationController extends Controller {

    public function configure($data) {
        if (is_object($data)) {
            foreach (get_object_vars($data) as $key => $val) {
                $this->model->set($key, $val);
            }
        } else if (is_array($data)) {
            foreach ($data as $key => $val) {
                $this->model->set($key, $val);
            }
        }
    }

    public function init() {
    }

    public function onGet() {
        $this->model->prepareNavStructure();
        $this->view->display($this->model->getConfiguration(), $this->model->getNavStructure());
    }

    public function onPost() {
        $this->onGet();
    }

}

?>
