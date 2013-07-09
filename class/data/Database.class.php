<?php

uses("core.exceptions.DatabaseException");
uses("core.base.Quobject");

/**
 * Class Database
 *
 * @var mixed[] $instance
 * @var object $_datasource_type
 * @var String $_host
 * @var String $_user
 * @var String $_pass
 * @var String $_database
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2008
 * @package core.data
 */
abstract class Database extends Quobject {
	private static $instance = array();
	private static $_datasource_type = NULL;
	protected static $_datasource_config = array();

	/**
	 * __clone
	 * 
	 * defending cloning
	 */
	protected function __clone() { }

	/**
	 * spawn the unique instance
	 */
	public static function getInstance($datasource_name,$config_array = NULL) {
		if (empty($datasource_name)) throw (new DatabaseException("Datasource name not specified."));

        if (count($config_array) == 0) {
            $config_array = self::$_datasource_config[$datasource_name];
        }

		if (count($config_array) != 0) {
			switch ($config_array['driver']) { 
				case "MySQL":
					$class_name = $config_array['driver'];
					$opt_arg = $config_array;
					uses ("core.data.".$class_name);
					break;
				case "DataTree":
					$class_name = 'SimpleXMLElement';
					$opt_arg='<doc/>';
					break;
				default: throw(new DatabaseException("Unknown/Unsupported database driver type '".$config_array['driver']."'."));
			}
		} else {
            throw(new DatabaseException("Database connection settings incomplete for datasource ".$datasource_name));
        }

		if (!array_key_exists($datasource_name, self::$instance)) {
			self::$instance[$datasource_name] = new $class_name($opt_arg);
			self::$_datasource_config[$datasource_name] = $config_array;
		}
		return self::$instance[$datasource_name];
	}
/*
    public static function getDataSourceConfiguration($string = null) {

        // something like MySQL://user:password@hostname/database_name
        if ( (is_null($string)) || (preg_match("/^([a-zA-Z]+):\/\/(a-zA-Z0-9-_\.):(a-zA-Z0-9-_\.)@(a-zA-Z0-9-_\.)\/(a-zA-Z0-9-_)$/", $string, $matches) == 0) ) {
            throw (new DatabaseException("Database connection settings incomplete for datasource ".$datasource_name));
        } else {
            var_dump($matches);
        }
    }
*/
	public static function getDataSourceConfiguration($datasource_name) {
		return self::$_datasource_config[$datasource_name];
	}

	/**
	 * connect
	 * 
	 * establish a database connection
	 *
	 * @protected
	 * @abstract
	 */
	abstract public function connect();

	abstract public function get($sql,$method="id");

	abstract public function query($sql);

	abstract public function num();

	abstract public function result($sql,$method="array");

	abstract public function fetch($result,$method="array");

    abstract public function free_memory();

}
