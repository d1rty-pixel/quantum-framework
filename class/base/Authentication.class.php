<?php

namespace core\base;

uses ("core.base.Quobject");

// eigenes session handling

/**
 * Authentication module
 *
 * To use this module you have to enable $config['use']['authentication'] in your application configuration (set it to true).
 * This Authentication is class is abstract, reuse it with some "app" Authentication class ($config['authentication']['class']). Please note that
 * parent::__construct(); has to be called.
 * 
 * If $config['authentication']['create'] is set to true the class defined in $config['authentication']['class'] is instanced automatically
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006-2008 Tristan Cebulla
 * @package core.base
 */
abstract class Authentication extends \core\base\Quobject {
	private $session = NULL;

	/**
	 * class constructor
	 */
	function __construct() {
        uses ("core.protocol.".\core\Quantum::registry("application.protocol").".Session");
		$this->session = \core\base\Singleton::getInstance('\core\base\Session');
		parent::__construct();
	}

	/** 
	 * logout user
	 *
	 * @return true
	 */
	public function logout() {
		if ($this->isAuth()) {
			$this->session->killSession();
		}
		return true;
	}

	/**
	 * auth a user to the session
	 *
	 * @param $username username
	 * @param $password password
	 * @param $extra e.g. password encryption method or anything you want (or nothing)
	 */
	abstract public function authUser($username="",$password="",$extra=NULL);

	abstract public function isAuth(); 

}

?>
