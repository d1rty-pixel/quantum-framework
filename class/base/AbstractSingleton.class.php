<?php

namespace core\base;

uses (
    "core.base.Quobject"
);

abstract class AbstractSingleton extends \core\base\Quobject {

    private static $instances = array();
 
    final public static function getInstance() {
        $class = get_called_class();
        if (empty(self::$instances[$class])) {
            $rc = new \ReflectionClass($class);
            self::$instances[$class] = $rc->newInstanceArgs(func_get_args());
        }
        return self::$instances[$class];
    }
 
    final public function __clone() {
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
