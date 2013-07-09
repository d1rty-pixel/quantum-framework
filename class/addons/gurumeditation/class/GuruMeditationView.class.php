<?php

namespace addon\gurumeditation;

uses (
    "core.base.mvc.View",
    "core.template.rainbow.RainbowTemplate"
);

class GuruMeditationView extends \core\base\mvc\View {

    private $types = array('Bool', 'Integer', 'Float', 'String', 'Array', 'Object', 'Null');

    public function __construct() {
        parent::__construct();
        $this->document->resetDocument();
    }

    private function cleanRegDump($object) {
        $s = "";
        if (\core\Quantum::registry("hide") != NULL) {
            foreach ($object as $id => $val) {
                if (in_array($id, \core\Quantum::registry("hide"))) {
                    $val = "**********";
                }
                $s .= $id.": ".$val."\n";
            }
        }
        return $s;
    }

    public function displayHeader() {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/header.xhtml", QUANTUM_ROOT));
    }

    public function displayFooter() {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/footer.xhtml", QUANTUM_ROOT));
    }

    public function displayMessage($exception_class, $exception_message, $trace) {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $tpl->assign(array(
            "exception_class"   => $exception_class,
            "message"           => $exception_message,
            "lines"             => substr_count($trace,"\n") + 1,
            "stacktrace"        => $trace,
            "id"                => sprintf("# %s", crc32($trace)),
        ));
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/message.xhtml", QUANTUM_ROOT));
    }

    public function displayStackTrace($stacktrace) {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $data = array();

        foreach ($stacktrace as $index => $trace) {
            $class_name = array_key_exists('class', $trace) ? $trace['class'].'::' : '';
            $str = "<? ".$class_name.$trace['function'].'(';

            $i=0;
            $non_string_args = array();

            foreach ($trace['args'] as $arg) {
                if (in_array((String) $arg, $this->types)) {
                    $str .= $i == 0 ? sprintf('%s', $arg) : sprintf(', %s', $arg);
                    $non_string_args[] = $arg;
                } else {
                    $str .= $i == 0 ? sprintf('"%s"', $arg) : sprintf(', "%s"', $arg);
                }
                $i++;
            }
            $str .= ") ?>";

            array_push($data, array(
                "file"      => $trace["file"],
                "line"      => $trace["line"],
                "index"     => $index,
                "string"    => highlight_String($str, true),
            ));
        }

        $tpl->assign("stacktrace", $data);
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/stacktrace.xhtml", QUANTUM_ROOT));
    }

    public function displayQLog($logs) {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $tpl->assign(array(
            "section"       => "Quantum Framework Log",
            "message"       => $logs,
        ));
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/section.xhtml", QUANTUM_ROOT));
    }

    public function displayErrors($string) {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $tpl->assign(array(
            "section"       => "PHP Runtime and Quantum Framework Application Errors",
            "message"       => $string,
        ));
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/section.xhtml", QUANTUM_ROOT));
    }

    public function dumpRegistry() {
        $tpl = new \core\template\rainbow\RainbowTemplate();
        $tpl->assign(array(
            "section"       => "Quantum Framework Registry Dump",
            "message"       => $this->cleanRegDump($reg),
        ));
        $tpl->draw(sprintf("%s/class/addons/gurumeditation/template/section.xhtml", QUANTUM_ROOT));
    }

}

?>
