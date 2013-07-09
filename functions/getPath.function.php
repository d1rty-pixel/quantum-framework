<?php

/**
  * getFQPath
  *
  * return the fully qualified path to a template, addon, class, module or cascading style sheet
  *
  * @param String $file
  * @param String $ident can be 'template', 'class', 'module' or 'css'
  * @exception GenericQuantumException
  * @return String $fq_path fully qualified path
  */
function getFQPath($file, $ident = null, $tpl_css = null) {
	switch ($ident) {
        case "addontemplate":
            $begin = QUANTUM_ROOT."/class/addons/".$tpl_css."/template/";
           # $begin = (!is_null($tpl_css)) ? QUANTUM_ROOT."/class/addon/" : $begin = Quantum::registry("path_templates")."/".$tpl_css."/";
            $end = ".".\core\Quantum::registry("application.output");
            break;
        case "addonclass":
            $begin = QUANTUM_ROOT."/class/addons/".$file."/class/";
            $file = ucfirst($file)."Addon";
            $end = ".class.php";
            break;
		case "template":
            if (preg_match("/struct/", $file)) {
    			$begin = \core\Quantum::registry("path.templates")."/";
            } else {
    			$begin = \core\Quantum::registry("path.templates")."/".$tpl_css."/";
            }
            $end = ".".\core\Quantum::registry("application.output");
#            echo Quantum::registry("project_output");
#			$end = ".tpl";
            break;
        case "class":
            $begin = \core\Quantum::registry("path.classes")."/";
            $end = ".class.php";
            break;
        case "module":
            $begin = \core\Quantum::registry("path.modules")."/";
            $end = ".php";
            break;
        case "css":
            $begin = \core\Quantum::registry("path.css")."/";
            $end = ".css";
            break;
        default:      throw (new \core\exceptions\GenericQuantumException("Unknown identifyer '$ident'.")); break;
    }
    
    if (is_int(strpos($file,".")) && !preg_match("/struct/", $file)) $file = str_replace(".","/",$file);

    $fq_path = $begin.$file.$end;
	trace("found path for ident '$ident' at '$fq_path'");
    return $fq_path;
}

?>
