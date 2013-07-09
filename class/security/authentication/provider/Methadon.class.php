<?php

namespace core\security\authentication\provider;

uses (
    "core.security.Authentication",
    "core.security.authentication.provider.MethadonToACL",
    "core.security.ACL"
);

class Methadon extends \core\security\Authentication {

    protected $tool_id          = null;

    protected $tool_secret      = null;

    protected $supported_vars   = array("tool_id", "tool_secret");

    public function __construct() {
        parent::__construct();

        # creates a new session
        $session = $this->getSessionClass();

        if (\Request::isEmpty("_sign_")) {
            if ($this->isAuthenticated()) {

                new \core\security\authentication\provider\MethadonToACL($session::get("methadon_tool_rights"), $this->acl);

                foreach ($this->parseRightsToRoles($_SESSION["methadon_tool_rights"]) as $role => $rights) {
                    $role = $this->acl->addRole($role);
                    $role->addRights($rights);
                }

            } else if (!$this->signOk() && !$this->isAuthenticated()) {
                $this->authenticate();
            }
        } else {
            $session::set("user_id",                \Request::getArgument("__person_id"));
            $session::set("user_name",              \Request::getArgument("__person_id"));
            $session::set("user_firstname",         \Request::getArgument("__person_id"));
            $session::set("user_lastname",          \Request::getArgument("__person_id"));
            $session::set("user_email",             \Request::getArgument("__person_id"));
            $session::set("methadon_tool_rights",   explode(",", \Request::getArgument("__right")));
            $session::set("methadon_session",       session_id());

            # automatically parses the __right parameter into roles/rights and adds these to the ACL
            new \core\security\authentication\provider\MethadonToACL(\Request::getArgument("__right"), $this->acl);
        }
    }

    private function parseRightsToRoles($array) {
        $roles = array();

        if (!is_array($array)) $array = explode(",", $array);

        foreach ($array as $item) {
            list($role_id, $right_name) = explode("|", $item);
            if (!isset($roles[$role_id]) || !is_array($roles[$role_id])) $roles[$role_id] = array();
            array_push($roles[$role_id], $right_name);
        }

        return $roles;
    }

    public function isAuthenticated() {
        $session = $this->getSessionClass();
        $methadon_session = $session::get("methadon_session");
        return !empty($methadon_session);
    }

    private function signOk() {

        // First, check sign - sign must also not be followed by other params
        if (FALSE === preg_match('/(.*)&_sign_=([a-zA-Z0-9]+)$/', $_SERVER["QUERY_STRING"], $regs))
            return false;
        if (!$regs[2] == md5($regs[1].'&'.$this->tool_secret))
            return false;

        # the timestamp arg must be there
        if (\Request::isEmpty("timestamp") || !preg_match("/^\d+$", \Request::getArgument("timestamp")))
            return false;

        # Parse given timestamp and compare to current time; timestamp
        # must not become older than 60 seconds to prevent replay-attacks
        if (\QDateTime::getTimestamp("now") - \Request::getArgument("timestamp") > 60)
            return false;

        // All ok, let user pass
        return true;
  }

    private function toolSignValid($toolSecret) {
        if (FALSE === preg_match('/(.*)&_sign_=([a-zA-Z0-9]+)/', $_SERVER['QUERY_STRING'], $regs)) return FALSE;
        if (sizeof($regs) == 0) return false;
        return (@$regs[2] == @md5($regs[1].'&'.$toolSecret)) ? TRUE : FALSE;
    }

    public function logout() {
        $session = $this->getSessionClass();
        $session::kill();
    }

    public function authenticate($username = null, $password = null) {
        $this->logout();

        ## build target section
        reset($_REQUEST);
        $target = "";
        while (list($key, $value) = each($_REQUEST)) {
            $target .= "&".$key."=".$value;
        }

        $args = array(
            "__project_id"  => $this->tool_id,
            "__jump"        => "/",
            "__requesttype" => "person_id,email,name,firstname",
            "__server"      => "http://asm.abuse.server.lan",
            "__target"      => urlencode($target),
            "__right"       => ",",
        );

        $location = "https://inside.1and1.org/authenticate/?";

        foreach ($args as $key => $value) {
            $location .= sprintf("&%s=%s", $key, $value);
        }

        Header("Location: ".$location);
    }
    
}

?>
