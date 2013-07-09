<?php

namespace core\debug;

/**
 * Class Debuglog
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006-2008 Tristan Cebulla
 * @package core.debug
 *
 * An element in a stack trace, as returned by Throwable::getStackTrace(). 
 * Each element represents a single stack frame.
 */
class StackTraceElement {
    var
      $file     = '',
      $class    = '',
      $method   = '',
      $line     = 0,
      $args     = array(),
      $message  = '';
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string file
     * @param   string class
     * @param   string method
     * @param   int line
     * @param   array args
     * @param   string message
     */
    function __construct($file, $class, $method, $line, $args, $message) {
      $this->file     = $file;  
      $this->class    = $class; 
      $this->method   = $method;
      $this->line     = $line;
      $this->args     = $args;
      $this->message  = $message;
    }
    
    /**
     * Create string representation
     *
     * @access  public
     * @return  string
     */
    function toString() {
      $args= array();
      if (isset($this->args)) {
        for ($j= 0, $a= sizeof($this->args); $j < $a; $j++) {
          if (is_array($this->args[$j])) {
            $args[]= 'array['.sizeof($this->args[$j]).']';
          } elseif (is_object($this->args[$j])) {
            $args[]= get_class($this->args[$j]).'{}';
          } elseif (is_string($this->args[$j])) {
            $display= str_replace('%', '%%', addcslashes(substr($this->args[$j], 0, min(
              (FALSE === $p= strpos($this->args[$j], "\n")) ? 0x40 : $p, 
              0x40
            )), "\0..\17"));
            $args[]= (
              '(0x'.dechex(strlen($this->args[$j])).")'".
              $display.
              "'"
            );
          } elseif (is_null($this->args[$j])) {
            $args[]= 'NULL';
          } else {
            $args[]= (string)$this->args[$j];
          }
        }
      }
      return sprintf(
        "  at %s::%s(%s) [line %d of %s] %s\n",
        isset($this->class) ? xp::nameOf($this->class) : '<main>',
        isset($this->method) ? $this->method : '<main>',
        implode(', ', $args),
        $this->line,
        basename(isset($this->file) ? $this->file : __FILE__),
        $this->message
      );
    }
  }
?>
