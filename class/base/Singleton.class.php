<?php

namespace core\base;

uses ("core.base.Quobject");

/**
 * Class Singleton
 *
 * singleton.
 * 
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.base
 *
 * @var mixed[] $instances array of instances 
 */
class Singleton extends \core\base\Quobject {
		
	static private $instances = array();

	/**
	 * getInstance
	 *
	 * @param String $className name of class
	 * @static
	 */
	static public function getInstance() {
		$class_name			= func_get_arg(0);
		$class_parameters	= array();

		for ($i = 1; $i < func_num_args(); $i++) {
			$class_parameters[$i - 1] = func_get_arg($i);
		}

		if (!isset(self::$instances[$class_name])) {
			self::$instances[$class_name] = new $class_name($class_parameters);
		}
		return self::$instances[$class_name];
	}

    static public function exists($class_name) {
        if (!isset(self::$instances[$class_name])) return false;
        return true;
    }

	/**	
	 * __clone
	 * 
	 * Defending cloning, the ninja style (no-one ever will see)
	 */
	private function __clone() {
/*
                                                 ..,.'.. .  ..           
            ..     .'.',.'''..'.... ...,.,,;,';ccloolllccc;cl.           
           ..,'',,,,,;;:clooooddolcccc:::;;;:::cllooooooolooocc'         
            .;::;;;;'...';clooddddoolc::;:ld:.. ....:cclloc,;;'          
            .'...,od;...,l::oddxxxdoooolclodo;'',;,:loo:,.               
             .'...,;:,;cclc;lddxkxdooxxdlcc:cclloooool,                  
             .,,,,;:cccclll:lddxkxddddxxdolllloodol:.                    
              .;:ccclllooolclddxkxddddxxxxxddd:..                        
               .,clooooooolclddxxxddddxxxxxo;.                           
                 .':loddddlcoodxdddddddlc.                               
                     '::'llloddddocc,.                                   
                         .:lllc:.                                        
                                                                         
                                                                         
                                                                         
                                                                         
               ..     ;Xk. ,k.               l0                          
           .:lOWd    :c.   ..                .                           
          .x, 0NN;  ;l     ,    .l.  .;     .,    .clkO,                 
              0 xW:'K   ,oN0  .,Xo ;lWx  :dxWX   .K,.NM.                 
             cd  :NW0   .'Ml    Kll..W;     Nk   Oc.okM, .'              
            .K.   .NW.   cMdo, ;W,  cMOo;   kl  cWoc .NKlc               
            ,'     :'    ,c.   ,.   .;      0.  :d.   .:                 
                                           :c                            
                                          'l                             
                                         ,;                              
                                      :;;.                               
*/
	}

}

?>
