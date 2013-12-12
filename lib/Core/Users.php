<?php

namespace Core;

use Core\Session;

class Users extends DataModel
{
	
	public function validPassword()
	{
		
		return $this->matchHash($this->inputPassword, $this->results[0]['password']);
		
	}
	
	public function verifyAuthenticated()
	{
		
		$session = new Session();
		
		if(!$session->get('userId'))
		{
			
			$this->notification('Not logged in', 'negative-notification');
			
			header('Location: ' . BASE_URL);
			exit;
			
		}
		
	}

}