<?php

uses ("core.base.mvc.ActiveRecordModel");

class SupernavigationModel extends ActiveRecordModel {

    protected $structure      = array();
    protected $level_depth    = null;
    protected $request        = null;

    protected $level_active   = array();
    protected $configuration  = array();

    public function set($key, $val) {
        $this->configuration[$key] = $val;
    }

    public function init() {
        $this->setDatasource("abuweb2");
        $this->request = new StdClass;

        preg_match("/^(https?):\/\/([^\/]*)\/([^\/]*)/", $_SERVER["SCRIPT_URI"], $matches);
        $this->request->protocol    = (!is_null($matches[1])) ? $matches[1] : null;
        $this->request->domain      = (!is_null($matches[2])) ? $matches[2] : null;
        $this->request->path        = (!is_null($matches[3])) ? $matches[3] : null;

        $this->level_depth = $this->getLevelDepth();
    }

    public function setLevelActive(Array $array) {
        $this->level_active = $array;
    }

    private function isActive($object) {
        # apply default function
        if (is_null($this->configuration["level_active"][$object->level])) {
            if ($this->request->protocol."://".$this->request->domain."/".$this->request->path == $object->uri) return true;
            return false;
        } else {
            return $this->configuration["level_active"][$object->level]($object->uri, $this->request);
        }
    }

    private function expandURI($uri) {
        if (is_object($uri)) {
            $uri->uri = $this->expandURI($uri->uri);
            return $uri;
        } else {
            # uri begins with /, add the current protocol and domain 
            if (preg_match("/^\/.*/", $uri)) {
                return $this->request->protocol."://".$this->request->domain.$uri;
            # default https? link
            } else if (preg_match("/^(https?):\/\/.*/", $uri)) {
                return $uri;
            }
        }
        # throw (new GenericQuantumException("fuck you"));
        return null;
    }

    private function getLevelRecords($array, $level) {
        return $array[$level];
    }

    private function getRecordsForTopID($data, $top_id) {
        if ($top_id == 0) return array();
        $result = array();
        foreach ($data as $level => $level_data) {
            foreach ($level_data as $record_id => $record) {
                if ($record->top_id == $top_id) {
                    $result[$record->id] = $record;
                }
            }
        }
        return $result;
    }

    public function getQueryArray() {
        if (!isset($this->configuration["site"])) { $this->configuration["site"] = "default"; }
        $where = array(
            "site = '".$this->configuration["site"]."'",
        );
        $array = array(
            "table"     => "abuweb2_navigation",
            "columns"   => array("id", "order_id", "level", "top_id", "description", "description_long", "uri", "img_path", "target", "right"),
            "order"     => array("level", "order_id"),
            "where"     => null,
        );
        if (isset($this->configuration["start_level"])) {
            array_push($where, "level >= ".$this->configuration["start_level"]);
        }

        if (count($where) == 1) {
            $array["where"] = $where;
        } else {
            $array["where"] = array("and" => $where);
        }

        return $array;
    }

    protected function getIDForURI($uri) {
        preg_match("/^(https?):\/\/([^\/]*)\/([^\/]*)/", $uri, $matches);
        $this->query("select id from abuweb2_navigation where ( site = '".$this->configuration["site"]."' and ( uri = '$uri' or uri = '/".$matches[3]."' ) );");
        $record = $this->next();
        return $record->id;
    }

    private function filterStructure($struct, $active_top_id) {
        $result = array();
        foreach ($struct as $record_id => $record) {
            if ((Int) $record->top_id == (Int) $active_top_id) {
                $result[$record_id] = $record; #$struct[$record_id];
            }
        }
        return $result;
    }

    public function prepareNavStructure() {
        $this->select($this->getQueryArray());
        $struct = array();

        while ($record = $this->next()) {
            $record         = $this->expandURI($record);
            # check for rights
            if ( (!empty($record->right)) && (!in_array($record->right, $_SESSION["intranet_tool_rights"]) )) {
                continue;
            }
            $record->subs   = array();
            if (!is_null($record->uri))             $record->active         = $this->isActive($record);

            if (!is_array($struct[$record->level])) $struct[$record->level] = array();
            $struct[$record->level][$record->id]                            = $record;
        }

        for ($level = $this->level_depth; $level >= 1; $level--) {
            foreach ($this->getLevelRecords($struct, $level) as $record_id => $record) {
                $record->subs = $this->getRecordsForTopID($struct, $record->id);
                $struct[$level][$record_id] = $record;
            }
        }

        # get active top_id
        $start_level    = 1;
        $active_top_id  = null;
        if (isset($this->configuration["start_level"])) {
            $start_level        = $this->configuration["start_level"];
            $active_top_id      = $this->getIDForURI($this->request->protocol."://".$this->request->domain."/".$this->request->path);
            $this->structure    = $this->filterStructure($struct[$start_level], $active_top_id);
        } else {
            $this->structure    = $struct[$start_level];
        }
    }

    public function getNavStructure() {
        return $this->structure;
    }

    public function getConfiguration() {
        return $this->configuration;
    }

    public function getLevelDepth() {
        if (is_null($this->level_depth)) {
            $array = $this->getQueryArray();
            $array["columns"] = array("max(level) as max");
            $this->select($array);
            $record = $this->next();
            $this->level_depth = (Int) $record->max;
        }
        return $this->level_depth;
    }

}

?>
