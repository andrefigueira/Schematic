<?php

namespace Core;

class Session extends General
{

	public $container = 'phpcms';

	public function get($key)
	{
		
		if(isset($_SESSION[$this->container][$key])){ return $_SESSION[$this->container][$key];}else{ return false;}
		
	}
	
	public function set($key, $value)
	{
		
		$_SESSION[$this->container][$key] = $value;
		
	}

	public function delete($key)
	{
		
		unset($_SESSION[$this->container][$key]);
		
	}

}