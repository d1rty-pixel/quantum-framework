<?php

namespace core\exceptions;

uses ("core.debug.StackTraceElement", "core.base.Singleton", "addon.gurumeditation.GuruMeditationAddon");

/**
 * Class GuruMeditationException
 *
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.exceptions
 *
 * @var String $message
 * @var mixed[] $trace 
 */
class GuruMeditationException extends \Exception {
	protected $message  = "";
    private $except     = array(
        'call_user_func_array'  => 1, 
        'call_user_func'        => 1, 
        'object'                => 1,
    );
    private $logger     = null;

    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     */
	public function __construct($message, $critical_hit = false) {
        parent::__construct($message);

        # write log when the exception is thrown
        $this->logger = \core\base\Singleton::getInstance('\core\log\LogFacility');
        trace("Catched exception with message: $message", $this);

		foreach (debug_backtrace() as $no => $trace) {
            if (!isset($trace['function']) || isset($this->except[$trace['function']])) continue;

            // Pop error messages off the copied error stack
            if (isset($trace['file']) && isset($errors[$trace['file']])) {
                $messages = $errors[$trace['file']];
                unset($errors[$trace['file']]);
            } else {
                $messages = array(array('' => 1));
            }

            // Not all of these are always set: debug_backtrace() should
            // initialize these - at least - to NULL, IMO => Workaround.
            $this->addStackTraceFor(
                isset($trace['file'])       ? $trace['file']        : NULL,
                isset($trace['class'])      ? $trace['class']       : NULL,
                isset($trace['function'])   ? $trace['function']    : NULL,
                isset($trace['line'])       ? $trace['line']        : NULL,
                isset($trace['args'])       ? $trace['args']        : NULL,
                $messages
            );
        }

        trace($this->getTraceAsString(), $this);
    }
   
    public function printException() {
        if (!$this->logger->writeLog()) {
            echo $this->logger->getMessages();
        }

        $addon = new \addon\gurumeditation\GuruMeditationAddon($this);
    }

    public function getErrorsAsString() {
        $error_map = array(
            "1"     => "E_ERROR",
            "2"     => "E_WARNING",
            "4"     => "E_PARSE",
            "8"     => "E_NOTICE",
            "16"    => "E_CORE_ERROR",
            "32"    => "E_CORE_WARNING",
            "64"    => "E_COMPILE_ERROR",
            "128"   => "E_COMPILE_WARNING",
            "256"   => "E_USER_ERROR",
            "512"   => "E_USER_WARNING",
            "1024"  => "E_USER_NOTICE",
            "6143"  => "E_ALL",
            "2048"  => "E_STRICT",
            "4096"  => "E_RECOVERABLE_ERROR",
            "8192"  => "E_DEPRECATED",
            "16384" => "E_USER_DEPRECATED",
        );

        $s = "";
        foreach ($GLOBALS["qf_errors"] as $id => $array) {
            $s .= sprintf("%s: '%s' in %s:%s\n",
                (isset($error_map[$array["code"]])) ? $error_map[$array["code"]] : $array["code"],
                $array["message"],
                $array["file"],
                $array["line"]
            );
        }
        return $s;
    }

    /**
     * Adds new stacktrace elements to the internal list of stacktrace
     * elements, each for one error.
     *
     * @access  protected
     * @param   string file
     * @param   string class
     * @param   string function
     * @param   int originalline
     * @param   mixed[] args
     * @param   mixed[] errors
     */
    protected function addStackTraceFor($file, $class, $function, $originalline, $args, $errors) {
        if (!is_array($errors)) return;

        foreach ($errors as $line => $errormsg) {
            foreach ($errormsg as $message => $amount) {
                $this->trace[] = new \core\debug\StackTraceElement(
                    $file,
                    $class,
                    $function,
                    $originalline ? $originalline : $line,
                    $args,
                    $message.($amount > 1 ? ' (... '.($amount - 1).' more)' : '')
                );
            }   
        }
    }

    /**
     * Return an array of stack trace elements
     *
     * @access  public
     * @return  lang.StackTraceElement[] array of stack trace elements
     * @see     xp://lang.StackTraceElement
     */
    public function getStackTrace() {
        return $this->trace;
    }

    /**
     * Print "stacktrace" to standard error
     *
     * @see     xp://lang.Throwable#toString
     * @param   resource fd default STDERR
     * @access  public
     */
    public function printStackTrace($fd= STDERR) {
      fputs($fd, $this->toString());
    }

    /**
     * Return formatted output of stacktrace
     *
     * Example:
     * <pre>
     * Exception lang.ClassNotFoundException (class "" [] not found)
     *   at lang.ClassNotFoundException::__construct((0x15)'class "" [] not found') \
     *   [line 79 of StackTraceElement.class.php] 
     *   at lang.ClassLoader::loadclass(NULL) [line 143 of XPClass.class.php] 
     *   at lang.XPClass::forname(NULL) [line 6 of base_test.php] \
     *   Undefined variable:  nam
     * </pre>
     *
     * @access  public
     * @return  string
     */
    public function toString() {
        $s = sprintf(
            "Exception %s (%s)\n",
            $this->getClassName(),
            $this->message
        );
        for ($i= 0, $t= sizeof($this->trace); $i < $t; $i++) {
            $s.= $this->trace[$i]->toString(); 
        }
        return $s;
	}

/*
	public function printHTMLGuruMeditation() {

		$logger = Singleton::getInstance("LogFacility");
    	Quantum::registry("debug_log",$logger->getMessages());

		echo '<html><head><title>make("GuruMeditation")</title>';
		$tmpvar = <<<TEMPVAR

            <style type="text/css">
            .guru_blink_on
            {   
                background-color:#A00000;
            }

            .guru_blink_off
            {   
                background-color:#000000;
                border-spacing:0px;
            }

            .guru_box_visible
            {   
				display:block;
				margin: 3px;
            }

            .guru_box_invisible
            {   
                display:none;
            }
            </style>

            <script language="JavaScript">
            var allPopped = false;
            function guru_blink(e)
            {

                guru_1 = document.getElementById ("guru_td1");
                guru_2 = document.getElementById ("guru_td2");
                guru_3 = document.getElementById ("guru_td3");
                guru_4 = document.getElementById ("guru_td4");
                if (guru_1.className=="guru_blink_on")
                {
                    guru_1.className="guru_blink_off";
                    guru_2.className="guru_blink_off";
                    guru_3.className="guru_blink_off";
                    guru_4.className="guru_blink_off";
                } else
                {
                    guru_1.className="guru_blink_on";
                    guru_2.className="guru_blink_on";
                    guru_3.className="guru_blink_on";
                    guru_4.className="guru_blink_on";
                }
                setTimeout("guru_blink()", 700);
                }

        function guru_popbox (e)
        {
            e = (e) ? e : (window.event) ? window.event : "";
            elem = (e.target) ? e.target : e.srcElement;
            while (elem.id.substr(0,13) != "guru_box_head")
            {
                elem = (elem.parentElement) ? elem.parentElement : elem.parentNode;

				            }

            if (elem.id.substr(0,13)== "guru_box_head" || elem.id.substr(0,13)== "guru_box_body")
            {
                elem2 = document.getElementById ( "guru_box_body" + elem.id.substr(13) );
                if (elem2.className=="guru_box_visible") elem2.className="guru_box_invisible";
                else elem2.className="guru_box_visible";
            }
        }
        function guru_popallboxes (e)
        {
            e = (e) ? e : (window.event) ? window.event : "";
            elem = (e.target) ? e.target : e.srcElement;
            if (allPopped == true) allPopped = false;
            else allPopped = true;
            i = 0;
            while (elem != null)
            {
                i++;
                elem = document.getElementById("guru_box_body"+i);
                if (elem != null)
                {
                    if (allPopped == true) elem.className="guru_box_visible"
                    else elem.className="guru_box_invisible";
                }
            }
                      }
                      </script>
TEMPVAR;
		echo $tmpvar;

		$ta_lines = substr_count($this->getMessage(),"\n") + 1;

		echo '</head><body style="background-color: black" onload="guru_blink(event)">';
		echo '<table onclick="guru_popallboxes(event)" style="cursor: pointer; width:100%; border:0px; margin:0px; padding:0px; border-spacing:0px;">
				<tr style="border-spacing:0px; border:0px; margin:0px; padding:0px; height:10px">
				<td style="border-spacing:0px; border:0px; margin:0px; padding:0px;" class="guru_blink_off" id="guru_td1" colspan="3"></td>
				</tr>
				<tr style="border-spacing:0px;border:0px; margin:0px; padding:0px; height:70px">
				<td style="border-spacing:0px; border:0px; margin:0px; padding:0px; width:10px" class="guru_blink_off" id="guru_td2" ></td>
				<td style="border-spacing:0px; border:0px; margin:0px; padding:0px; vertical-align:top; background-color:#000000 ;text-align: center; color:#A00000"><span style="font-size:22pt; letter-spacing:3;"><b>Guru Meditation</b><br /><b>Quantum Software Failure</b></span><br><br><textarea style="background-color:#000;border:0;font-size:10px;color:#FFF;width:95%;" readonly="readonly" rows="'.$ta_lines.'">'.$this->getMessage().' </textarea></td>
				<td style="width:10px" class="guru_blink_off" id="guru_td3"></td>
				</tr> 
				<tr style="border-spacing:0px;border:0px; margin:0px; padding:0px; height:10px">
				<td style="border-spacing:0px; border:0px; margin:0px; padding:0px;" class="guru_blink_off" id="guru_td4" colspan="3"></td>
				</tr>
				</table>';

		echo "<br/>";

		$j=1;
		foreach($this->message->getTrace() as $line) {
			$class_name = array_key_exists('class', $line) ? $line['class'].'::' : '';
			$str = "<?php \n".$class_name.$line['function'].'(';

			$types = array('Bool', 'Integer', 'Float', 'String', 'Array', 'Object', 'Null');
			$i=0;
			$non_string_args = array();
			foreach($line['args'] as $arg ) {
				if(in_array((String)$arg, $types)) {
					$str .= $i == 0 ? sprintf('%s', $arg) : sprintf(', %s', $arg);
					$non_string_args[] = $arg;
				} else {
					$str .= $i == 0 ? sprintf('"%s"', $arg) : sprintf(', "%s"', $arg);
				}
				
				$i++;
			}
			$str .= ")\n?>";

			echo "<pre id=\"guru_box_head$j\"".' onclick="guru_popbox(event)" style="cursor: pointer; border: 1px dashed #3d3d00; background-color: white; margin-top: 5px; ">';
			echo '<i>in file <b>'.$line['file'].'</b> line <b>'.$line['line'].'</b>:<br/>';
		
			if ($j==1)
				echo "<div id=\"guru_box_body$j\" ".' class="guru_box_visible" style="background-color: #95ff5c">';
			else
				echo "<div id=\"guru_box_body$j\" ".' class="guru_box_invisible" style="background-color: #95ff5c">';

			highlight_string($str);
			echo '</div>';
			echo '</pre>';
			$j++;
		}

		echo '<div><pre style="border: 1px solid black; background-color: lightblue; padding: 5px; margin: 5px"><b>Quantum Registry dump:</b>';
		echo '<div style="background-color: lightyellow">';
		$reg_dump = Quantum::registry();
		echo $this->cleanRegDump($reg_dump);
		echo '</div></pre></div>';

		echo '<div><pre style="border: 1px solid black; background-color: lightblue; padding: 5px; margin: 5px"><b>Class uses cache:</b>';
		echo '<div style="background-color: lightyellow">';
		sort($GLOBALS['uses_cache']);
		echo var_dump($GLOBALS['uses_cache']);
		echo '</div></pre></div>';

            echo '</body></html>';

	}
*/

}

?>
