<?php

namespace core\template\rainbow;

uses (
    "core.template.Template",
    "core.template.rainbow.RainbowTemplateCache"
);

class RainbowCompiler extends \core\template\Template {

    protected $template_info        = array();

    private $php_enabled            = false;

    private $remove_comments        = true;

    private $auto_escape            = true;

    private $sandbox                = true;

    private $template               = null;

    // tags registered by the developers
    protected $registered_tags = array();

    // tags natively supported
    protected $tags = array(
        'loop'              => array('({loop.*?})', '/{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/'),
        'loop_close'        => array('({\/loop})', '/{\/loop}/'),
        'loop_break'        => array('({break})', '/{break}/'),
        'loop_continue'     => array('({continue})', '/{continue}/'),
        'if'                => array('({if.*?})', '/{if="([^"]*)"}/'),
        'elseif'            => array('({elseif.*?})', '/{elseif="([^"]*)"}/'),
        'else'              => array('({else})', '/{else}/'),
        'if_close'          => array('({\/if})', '/{\/if}/'),
        'noparse'           => array('({noparse})', '/{noparse}/'),
        'noparse_close'     => array('({\/noparse})', '/{\/noparse}/'),
        'ignore'            => array('({ignore}|{\*)', '/{ignore}|{\*/'),
        'ignore_close'      => array('({\/ignore}|\*})', '/{\/ignore}|\*}/'),
        'include'           => array('({include.*?})', '/{include="([^"]*)"}/'),
        'function'          => array('({function.*?})', '/{function="([a-zA-Z_][a-zA-Z_0-9\:]*)(\(.*\)){0,1}"}/'),
        'variable'          => array('({\$.*?})', '/{(\$.*?)}/'),
        'constant'          => array('({#.*?})', '/{#(.*?)#{0,1}}/'),
        'module'            => array('({module.*?})','/{module="([a-zA-Z_][a-zA-Z_0-9\:]*)(\(.*\)){0,1}"}/'),
        'main_content'      => array('({main_content})', '/{main_content}/'),
        'plugin'            => array('({plugin.*?})', '/{plugin="([^"]*)"\s*?(?:model="([^"]*)")?}/'),
        'registry'          => array('({registry.*?})', '/registry="([^"]*)"}/'),
        'config'            => array('({config.*?})', '/config="([^"]*)"}/'),

    );


    // black list of functions and variables
    protected $black_list = array(
        'exec', 'shell_exec', 'pcntl_exec', 'passthru', 'proc_open', 'system',
        'posix_kill', 'posix_setsid', 'pcntl_fork', 'posix_uname', 'php_uname',
        'phpinfo', 'popen', 'file_get_contents', 'file_put_contents', 'rmdir',
        'mkdir', 'unlink', 'highlight_contents', 'symlink',
        'apache_child_terminate', 'apache_setenv', 'define_syslog_variables',
        'escapeshellarg', 'escapeshellcmd', 'eval', 'fp', 'fput',
        'ftp_connect', 'ftp_exec', 'ftp_get', 'ftp_login', 'ftp_nb_fput',
        'ftp_put', 'ftp_raw', 'ftp_rawlist', 'highlight_file', 'ini_alter',
        'ini_get_all', 'ini_restore', 'inject_code', 'mysql_pconnect',
        'openlog', 'passthru', 'php_uname', 'phpAds_remoteInfo',
        'phpAds_XmlRpc', 'phpAds_xmlrpcDecode', 'phpAds_xmlrpcEncode',
        'posix_getpwuid', 'posix_kill', 'posix_mkfifo', 'posix_setpgid',
        'posix_setsid', 'posix_setuid', 'posix_uname', 'proc_close',
        'proc_get_status', 'proc_nice', 'proc_open', 'proc_terminate',
        'syslog', 'xmlrpc_entity_decode'
    );

    private $black_list_preg        = null;

    protected $document               = null;

    public function __construct($template) {
        $this->template = &$template;
        parent::__construct();
        $this->cache    = \core\base\Singleton::getInstance('\core\template\rainbow\RainbowTemplateCache');
    }

    /**
     * Assign variable
     * eg.     $t->assign('name','mickey');
     *
     * @param mixed $variable Name of template variable or associative array name/value
     * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
     *
     * @return \Rain\Tpl $this
     */
    public function assign($variable, $value = null) {
        if (is_array($variable))
            $this->var = $variable + $this->var;
        else
            $this->var[$variable] = $value;

        return $this;
    }  


    /**
     * Compile the file and save it in the cache
     *
     * @param string $templateName: name of the template
     * @param string $templateBaseDir
     * @param string $templateDirectory
     * @param string $templateFilepath
     * @param string $parsedTemplateFilepath: cache file where to save the template
     */
    public function compileFile(
        $templateName,
        $templateBasedir,
        $templateDirectory,
        $templateFilepath,
        $parsedTemplateFilepath
    ) {


        // open the template
        $fp = fopen($templateFilepath, "r") or die("cannot open file $templateFilepath");

        // lock the file
        if (flock($fp, LOCK_SH)) {

            // save the filepath in the info
            $this->templateInfo['template_filepath'] = $templateFilepath;

            // read the file
            $this->templateInfo['code'] = $code = fread($fp, filesize($templateFilepath));

            // xml substitution
            $code = preg_replace("/<\?xml(.*?)\?>/s", /*<?*/ "##XML\\1XML##", $code);

            // disable php tag
            if (!$this->php_enabled)
                $code = str_replace(array("<?", "?>"), array("&lt;?", "?&gt;"), $code);

            // xml re-substitution
            $code = preg_replace_callback("/##XML(.*?)XML##/s", function( $match ) {
                        return "<?php echo '<?xml " . stripslashes($match[1]) . " ?>'; ?>";
                    }, $code);

            $parsedCode = $this->compileTemplate($code, $isString = false, $templateBasedir, $templateDirectory, $templateFilepath);


/*            $parsedCode = "<?php if(!class_exists('\\core\\rtpl\\RainbowTemplate')){exit;}?>" . $parsedCode;*/


            // fix the php-eating-newline-after-closing-tag-problem
/*            $parsedCode = str_replace("?>\n", "?>\n\n", $parsedCode);*/

            // check if the cache is writable
            if (!is_writeable($this->cache->getCacheDir())) 
#            if (!is_writable($this->config['cache_dir']))
                throw new \core\exceptions\OperationNotPermittedException('Cache directory ' . $this->cache->getCacheDir() . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to FALSE. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

            $this->cache->write($parsedTemplateFilepath, $parsedCode);

            // release the file lock
            flock($fp, LOCK_UN);
        }

        // close the file
        fclose($fp);
    }


    /**
     * Compile a string and save it in the cache
     *
     * @param string $templateName: name of the template
     * @param string $templateBaseDir
     * @param string $templateFilepath
     * @param string $parsedTemplateFilepath: cache file where to save the template
     * @param string $code: code to compile
     */
    public function compileString($templateName, $templateBasedir, $templateFilepath, $parsedTemplateFilepath, $code) {

        // open the template
        $fp = fopen($parsedTemplateFilepath, "w");

        // lock the file
        if (flock($fp, LOCK_SH)) {

            // xml substitution
            $code = preg_replace("/<\?xml(.*?)\?>/s", "##XML\\1XML##", $code);

            // disable php tag
            if (!$this->php_enabled)
                $code = str_replace(array("<?", "?>"), array("&lt;?", "?&gt;"), $code);

            // xml re-substitution
            $code = preg_replace_callback("/##XML(.*?)XML##/s", function( $match ) {
                        return "<?php echo '<?xml " . stripslashes($match[1]) . " ?>'; ?>";
                    }, $code);

            $parsedCode = $this->compileTemplate($code, $isString = true, $templateBasedir, $templateDirectory = null, $templateFilepath);
/*            $parsedCode = "<?php if(!class_exists('\\core\\rtpl\\RainbowTemplate')){exit;}?>" . $parsedCode; */

            // fix the php-eating-newline-after-closing-tag-problem
            $parsedCode = str_replace("?>\n", "?>\n\n", $parsedCode);

            // create directories
#ä            if (!is_dir($this->config['cache_dir']))
#ä                mkdir($this->config['cache_dir'], 0755, true);

            // check if the cache is writable
            if (!is_writable($this->cache->getCacheDir()))
                throw new \core\exceptions\OperationNotPermittedException('Cache directory ' . $this->cache->getCacheDir() . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to false. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

            // write compiled file
            fwrite($fp, $parsedCode);

            // release the file lock
            flock($fp, LOCK_UN);
        }

        // close the file
        fclose($fp);
    }

    /**
     * Compile template
     * @access protected
     *
     * @param string $code: code to compile
     */
    public function compileTemplate($code, $isString, $templateBasedir, $templateDirectory, $templateFilepath) {

        // Execute plugins, before_parse
        $context = $this->template->getPlugins()->createContext(array(
            'code' => $code,
            'template_basedir' => $templateBasedir,
            'template_filepath' => $templateFilepath,
            'conf' => $this->config,
        ));

        $this->template->getPlugins()->run('beforeParse', $context);
        $code = $context->code;

        // set tags
        foreach ($this->tags as $tag => $tagArray) {
            list( $split, $match ) = $tagArray;
            $tagSplit[$tag] = $split;
            $tagMatch[$tag] = $match;
        }

        $keys = array_keys($this->registered_tags);
        $tagSplit += array_merge($tagSplit, $keys);


        //Remove comments
        if ($this->remove_comments) {
            $code = preg_replace('/<!--(.*)-->/Uis', '', $code);
        }

        //split the code with the tags regexp
        $codeSplit = preg_split("/" . implode("|", $tagSplit) . "/", $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        //variables initialization
        $parsedCode = $comentIsOpen = $ignoreIsOpen = NULL;
        $openIf = $loopLevel = 0;

        // if the template is not empty
        if ($codeSplit)

        //read all parsed code
            foreach ($codeSplit as $html) {

                //close ignore tag
                if (!$comentIsOpen && preg_match($tagMatch['ignore_close'], $html))
                    $ignoreIsOpen = FALSE;

                //code between tag ignore id deleted
                elseif ($ignoreIsOpen) {
                    //ignore the code
                }

                //close no parse tag
                elseif (preg_match($tagMatch['noparse_close'], $html))
                    $comentIsOpen = FALSE;

                //code between tag noparse is not compiled
                elseif ($comentIsOpen)
                    $parsedCode .= $html;

                //ignore
                elseif (preg_match($tagMatch['ignore'], $html))
                    $ignoreIsOpen = TRUE;

                //noparse
                elseif (preg_match($tagMatch['noparse'], $html))
                    $comentIsOpen = TRUE;

                //include tag
                elseif (preg_match($tagMatch['include'], $html, $matches)) {

                    $matches[1] = $this->varReplace($matches[1], $loopLevel); 
                    $parsedCode .=  '<?php require $this->checkTemplate("'.$matches[1].'");?>';
/*
                    print_r($matches);

                    //get the folder of the actual template
                    $actualFolder = substr($templateDirectory, strlen($this->config['tpl_dir']));

                    print "actualFolder: $actualFolder";

                    //get the included template
                    $includeTemplate = $actualFolder . $this->varReplace($matches[1], $loopLevel);

                    // reduce the path 
                    $includeTemplate = $this->reducePath( $includeTemplate );

                    print "includeTemplate: $includeTemplate";
 
                    //dynamic include
                    $parsedCode .= '<?php require $this->checkTemplate("' . $includeTemplate . '");?>';
*/
                }

                //loop
                elseif (preg_match($tagMatch['loop'], $html, $matches)) {

                    // increase the loop counter
                    $loopLevel++;

                    //replace the variable in the loop
                    $var = $this->varReplace($matches['variable'], $loopLevel - 1, $escape = FALSE);

                    if (preg_match('#\(#', $var)) {
                        $newvar = "\$newvar{$loopLevel}";
                        $assignNewVar = "$newvar=$var;";
                    } else {
                        $newvar = $var;
                        $assignNewVar = null;
                    }

                    // check black list
                    $this->blackList($var);

                    //loop variables
                    $counter = "\$counter$loopLevel";       // count iteration

                    if (isset($matches['key']) && isset($matches['value'])) {
                        $key = $matches['key'];
                        $value = $matches['value'];
                    } elseif (isset($matches['key'])) {
                        $key = "\$key$loopLevel";               // key
                        $value = $matches['key'];
                    } else {
                        $key = "\$key$loopLevel";               // key
                        $value = "\$value$loopLevel";           // value
                    }

                    //loop code
                    $parsedCode .= "<?php $counter=-1; $assignNewVar if( isset($newvar) && ( is_array($newvar) || $newvar instanceof Traversable ) && sizeof($newvar) ) foreach( (array) $newvar as $key => $value ){ $counter++; ?>";

                }

                //close loop tag
                elseif (preg_match($tagMatch['loop_close'], $html)) {

                    //iterator
                    $counter = "\$counter$loopLevel";

                    //decrease the loop counter
                    $loopLevel--;

                    //close loop code
                    $parsedCode .= "<?php } ?>";
                }

                //break loop tag
                elseif (preg_match($tagMatch['loop_break'], $html)) {
                    //close loop code
                    $parsedCode .= "<?php break; ?>";
                }

                //continue loop tag
                elseif (preg_match($tagMatch['loop_continue'], $html)) {
                    //close loop code
                    $parsedCode .= "<?php continue; ?>";
                }

                //if
                elseif (preg_match($tagMatch['if'], $html, $matches)) {

                    //increase open if counter (for intendation)
                    $openIf++;

                    //tag
                    $tag = $matches[0];

                    //condition attribute
                    $condition = $matches[1];

                    // check black list
                    $this->blackList($condition);

                    //variable substitution into condition (no delimiter into the condition)
                    $parsedCondition = $this->varReplace($condition, $loopLevel, $escape = FALSE);

                    //if code
                    $parsedCode .= "<?php if( $parsedCondition ){ ?>";
                }

                //elseif
                elseif (preg_match($tagMatch['elseif'], $html, $matches)) {

                    //tag
                    $tag = $matches[0];

                    //condition attribute
                    $condition = $matches[1];

                    // check black list
                    $this->blackList($condition);

                    //variable substitution into condition (no delimiter into the condition)
                    $parsedCondition = $this->varReplace($condition, $loopLevel, $escape = FALSE);

                    //elseif code
                    $parsedCode .=  "<?php }elseif( $parsedCondition ){ ?>";
                }

                //else
                elseif (preg_match($tagMatch['else'], $html)) {

                    //else code
                    $parsedCode .= '<?php }else{ ?>';
                }

                //close if tag
                elseif (preg_match($tagMatch['if_close'], $html)) {

                    //decrease if counter
                    $openIf--;

                    // close if code
                    $parsedCode .= '<?php } ?>';
                }

                // function
                elseif (preg_match($tagMatch['function'], $html, $matches)) {

                    // get function
                    $function = $matches[1];

                    // var replace
                    if (isset($matches[2]))
                        $parsedFunction = $function . $this->varReplace($matches[2], $loopLevel, $escape = FALSE, $echo = FALSE);
                    else
                        $parsedFunction = $function . "()";

                    // check black list
                    $this->blackList($parsedFunction);

                    // function
                    $parsedCode .=  "<?php echo $parsedFunction; ?>";
                }


                // module
                elseif (preg_match($tagMatch['module'], $html, $matches)) {
            
                    $module = $matches[1];

##                    // var replace
#                    if (isset($matches[2]))
#                        $parsedFunction = $module . $this->varReplace($matches[2], $loopLevel, $escape = FALSE, $echo = FALSE);
 #                   else
 #                       $parsedFunction = $function . "()";
#
#                    $this->blackList($parsedFunction);

                    $parsedCode .= sprintf('<?php include "%s/%s.php"; ?>', $this->config->get("path.modules"), $module);


                }
                elseif (preg_match($tagMatch['main_content'], $html, $matches)) {

                    trace("Processing main_content", $this);

                    $module_path        = null;
                    $template_path      = null;
                    $parameter_value    = \Request::getArgument($this->config->get("default.parameter"));

                    $mc_ok = false;

                    if (!$mc_ok) {
                        try {
                            $module_path = $this->getModulePath($parameter_value);
                            trace("Trying to load module $parameter_value from $module_path", $this);
                            $parsedCode .= sprintf('<?php include "%s"; ?>', $module_path);
                            $mc_ok = true;
                        } catch (\core\exceptions\FileNotFoundException $e) {
                        }
                    }

                    if (!$mc_ok) {
                        try { 
                            $template_path = $this->getTemplatePath($parameter_value);
                            $parsedCode .=  '<?php require $this->checkTemplate("'.$template_path.'");?>';
                            $mc_ok = true;
                        } catch (\core\exceptions\FileNotFoundException $e) {
                        }
    
                    }

                    if (!$mc_ok) {
                        print "bin hier";
                    }
                }

                elseif (preg_match($tagMatch['plugin'], $html, $matches)) {

                    uses ($matches[1], $matches[2]);
                    $plugin_name = sprintf("\\%s", str_replace(".", "\\", $matches[1]));
                    $plugin = new $plugin_name;
                    $plugin->setOption("model", $matches[2]);

                    foreach ($plugin->declareHooks() as $method) {
                        $unparsedCode = $plugin->$method($parsedCode);
                        $parsedCode = $this->template->drawString($unparsedCode);
                    }

                }

                elseif (preg_match($tagMatch['config'], $html, $matches)) {

                    $parsedCode .= sprintf('<?php echo "%s"; ?>', $this->config->get($matches[1]));

                }
                elseif (preg_match($tagMatch['registry'], $html, $matches)) {

                    $parsedCode .= sprintf('<?php echo \\core\\Quantum::registry("%s"); ?>', $matches[1]);

                  }
 
                //variables
                elseif (preg_match($tagMatch['variable'], $html, $matches)) {
                    //variables substitution (es. {$title})
                    $parsedCode .= "<?php " . $this->varReplace($matches[1], $loopLevel, $escape = TRUE, $echo = TRUE) . "; ?>";
                }


                //constants
                elseif (preg_match($tagMatch['constant'], $html, $matches)) {
                    $parsedCode .= "<?php echo " . $this->conReplace($matches[1], $loopLevel) . "; ?>";
                }
                // registered tags
                else {
 
                    $found = FALSE;
                    foreach ($this->registered_tags as $tags => $array) {
                        if (preg_match_all('/' . $array['parse'] . '/', $html, $matches)) {
                            $found = true;
                            $parsedCode .= "<?php echo call_user_func( $this->registered_tags['$tags']['function'], " . var_export($matches, 1) . " ); ?>";
                        }
                    }

                    if (!$found){
                        $parsedCode .= $html;
                    }
                }
            }


        if ($isString) {
            if ($openIf > 0) {

                $trace = debug_backtrace();
                $caller = array_shift($trace);

                throw new \core\excetions\TemplateSyntaxException("Error! You need to close an {if} tag in the string, loaded by {$caller['file']} at line {$caller['line']}");
            }

            if ($loopLevel > 0) {

                $trace = debug_backtrace();
                $caller = array_shift($trace);
                throw new \core\exceptions\TemplateSyntaxException("Error! You need to close the {loop} tag in the string, loaded by {$caller['file']} at line {$caller['line']}");
            }
        } else {
            if ($openIf > 0) {
                throw new \core\exceptions\TemplateSyntaxException("Error! You need to close an {if} tag in $templateFilepath template");
            }

            if ($loopLevel > 0) {
                throw new \core\exceptions\TemplateSyntaxException("Error! You need to close the {loop} tag in $templateFilepath template");
            }
        }

        // Execute plugins, after_parse
        $context->code = $parsedCode;
        $this->template->getPlugins()->run('afterParse', $context);

        return $context->code;
    }

    protected function conReplace($html) {
        $html = $this->modifierReplace($html);
        return $html;
    }

    protected function modifierReplace($html) {
        
        $this->blackList($html);
        if (strpos($html,'|') !== false && substr($html,strpos($html,'|')+1,1) != "|") {
            preg_match('/([\$a-z_A-Z0-9\(\),\[\]"->]+)\|([\$a-z_A-Z0-9\(\):,\[\]"->]+)/i', $html,$result);

            $function_params = $result[1];
            $explode = explode(":",$result[2]);
            $function = $explode[0];
            $params = isset($explode[1]) ? "," . $explode[1] : null;

            $html = str_replace($result[0],$function . "(" . $function_params . "$params)",$html);

            if (strpos($html,'|') !== false && substr($html,strpos($html,'|')+1,1) != "|") {
                $html = $this->modifierReplace($html);
            }
        }

        return $html;
    }

    protected function blackList($html) {
        if (!$this->sandbox || !$this->black_list)
            return true;

        if (empty($this->black_list_preg))
            $this->black_list_preg = '#[\W\s]*' . implode('[\W\s]*|[\W\s]*', $this->black_list) . '[\W\s]*#';

        // check if the function is in the black list (or not in white list)
        if (preg_match($this->black_list_preg, $html, $match)) {

            // find the line of the error
            $line = 0;
            $rows = explode("\n", $this->templateInfo['code']);
            while (!strpos($rows[$line], $html) && $line + 1 < count($rows))
                $line++;

            // stop the execution of the script
            throw new \core\exceptions\TemplateSyntaxException('Syntax ' . $match[0] . ' not allowed in template: ' . $this->templateInfo['template_filepath'] . ' at line ' . $line);
            return false;
        }
    }

    protected function varReplace($html, $loopLevel = NULL, $escape = TRUE, $echo = FALSE) {

        // change variable name if loop level
        if (!empty($loopLevel))
            $html = preg_replace(array('/(\$key)\b/', '/(\$value)\b/', '/(\$counter)\b/'), array('${1}' . $loopLevel, '${1}' . $loopLevel, '${1}' . $loopLevel), $html);

        // if it is a variable
        if (preg_match_all('/(\$[a-z_A-Z][^\s]*)/', $html, $matches)) {
            // substitute . and [] with [" "]
            for ($i = 0; $i < count($matches[1]); $i++) {

                $rep = preg_replace('/\[(\${0,1}[a-zA-Z_0-9]*)\]/', '["$1"]', $matches[1][$i]);
                //$rep = preg_replace('/\.(\${0,1}[a-zA-Z_0-9]*)/', '["$1"]', $rep);
                $rep = preg_replace( '/\.(\${0,1}[a-zA-Z_0-9]*(?![a-zA-Z_0-9]*(\'|\")))/', '["$1"]', $rep );
                $html = str_replace($matches[0][$i], $rep, $html);
            }

            // update modifier
            $html = $this->modifierReplace($html);

            // if does not initialize a value, e.g. {$a = 1}
            if (!preg_match('/\$.*=.*/', $html)) {

                // escape character
                if ($this->auto_escape && $escape)
                //$html = "htmlspecialchars( $html )";
                    ## FIXME!!!! das muss wieder rein
#                    $html = "htmlspecialchars( $html, ENT_COMPAT, '" . \core\Quantum::registry("application.charset") . "', FALSE )";

                // if is an assignment it doesn't add echo
                if ($echo)
                    $html = "echo " . $html;
            }
        }

        return $html;
    }

    public function reducePath( $path ){
        // reduce the path
        $path = str_replace( "://", "@not_replace@", $path );
        $path = preg_replace( "#(/+)#", "/", $path );
        $path = preg_replace( "#(/\./+)#", "/", $path );
        $path = str_replace( "@not_replace@", "://", $path );

        while( preg_match( '#\.\./#', $path ) ){
            $path = preg_replace('#\w+/\.\./#', '', $path );
        }
        return $path;
    }



}

?>
