<?php

namespace Core;

class General
{
	
	public function ob()
	{
	
		ob_start(array(
            'Core\General', 
            'processHtml'
        ));
		
        ob_clean();
        
	}
	
	public function getVar($name, $method = 'post', $santize = true)
	{
		
		switch($method)
		{
			
			case 'post':
			
				if(isset($_POST[$name]))
				{
			
					if($_POST[$name] == '')
					{ 
						
						return false;
					
					}
					else
					{ 
						
						if($santize)
						{ 
							
							return $this->sanitize($_POST[$name]);
							
						}
						else
						{ 
								
							return $_POST[$name];
								
						}
					
					}
					
				}
				else
				{
					
					return false;
					
				}
				
				break;
				
			case 'get':
			
				if(isset($_GET[$name]))
				{
				
					if($_GET[$name] == '')
					{ 
						
						return false;
					
					}
					else
					{ 
						
						if($santize)
						{ 
							
							return $this->sanitize($_GET[$name]);
							
						}
						else
						{ 
							
							return $_GET[$name];
						
						}
						
					}
					
				}
				else
				{
					
					return false;
					
				}
				
				break;
			
			default:
			
				return null;
			
		}
		
	}
	
	public function processHtml($html)
	{
		
		if(MINIFY){ $html = $this->minifyHtml($html);}
		
		preg_match_all('/\{\{(.*?)\}\}/', $html, $matches);
		
		$defaultPlaceholders = array(
			'{{baseUrl}}' => BASE_URL,
			'{{year}}' => date('Y')
		);
			
		$html = str_replace(array_keys($defaultPlaceholders), $defaultPlaceholders, $html);
		
		foreach($matches[1] as $key => $value)
		{
			
			$html = str_replace('{{'.$value.'}}', 'REPLACE VAR', $html);
			
		}
		
		return $html;
		
	}
		
	/*
	* Minify HTML, this removes unessasary space
	* @access public
	* @return any
	*/
	public function minifyHtml($content)
	{
		
		return preg_replace('(\r|\n|\t)', '', $content);
		
	}
	
	public function parse($array)
	{
		
		echo '<pre>';
		print_r($array);
		echo '</pre>';
		
	}
	
	public function sanitize($str)
	{
	
		$str = urldecode($str);
		$str = addslashes($str);
		$str = htmlspecialchars($str);
		
		return $str;
		
	}
	
	public function json($array = array())
	{
	
		header('Content-Type: application/json');
		
		echo json_encode($array);
		exit;
		
	}

	/*
	* Creates a hashed string using salt
	* @access public
	* @var $str (string)
	* @return string
	*/
	public function hashStr($str) 
	{
	
    	if(defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) 
	    {
	    
	        $salt = '$2y$13$'.substr(md5(uniqid(rand(), true)), 0, 22);
	        
	        return crypt($str, $salt);
	        
	    }
	    
	}

	/*
	* Checks if the inputted string matches the hashed version
	* @access public
	* @var $str (string)
	* @var $str (string)
	* @return bool
	*/
	public function matchHash($str, $hashedStr)
	{
		
		return crypt($str, $hashedStr) == $hashedStr;
		
	}

	/*
	* Checks if the $stmt is false, indicating a failed query
	* @access public
	* @var $stmt (bool)
	* @var $message (string)
	* @return string
	*/
	public function handleResult($stmt, $message = 'An error has occured...')
	{
		
		if(!$stmt)
		{
			
			$this->json(array(false, $message));
			
		}
		
	}

	/*
	* Returns datetime formatted string of the current date and time
	* @access public
	* @return string
	*/
	public function datetime()
	{
		
		return date('Y-m-d H:i:s');
		
	}
	
	public function notification($content, $classes = 'positive-notification')
	{
		
		$session = new Session();
		$session->set('notification', serialize(array(
			'content' => $content,
			'classes' => $classes
		)));
		
	}
	
	public function printNotification()
	{
		
		$session = new Session();
		$view = new View();
		$view->template('site-notification');
		
		$notification = $session->get('notification');
		
		if(!$notification)
		{
			
			$notification = unserialize($notification);
			
			$html = $view->parse(array(
				'classes' => $notification['classes'],
				'content' => $notification['content']
			));

			$session->delete('notification');
			
			echo $html;
			
		}
		
	}

	public function getFile($path)
	{
		
		$str = @file_get_contents($path);
		
		if($str === false) 
		{
		
			throw new \Exception("Cannot access '$path' to read contents.");
			
		} 
		else 
		{
		
			return $str;
			
		}
		
	}
	
	public function timeDifference($date)
	{
		
		if(empty($date)) 
		{
			
			die('A date is needed...');
			
		}
	
		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60","60","24","7","4.35","12","10");
	
		$now = time();
		$unixDate = strtotime($date);
	
		//Check validity of date
		if(empty($unixDate)) 
		{   
			
			die('Invalid date');
			
		}
	
		//Is it future date or past date
		if($now > $unixDate) 
		{   
			$difference = $now - $unixDate;
			$tense = "ago";
	
		} 
		else 
		{
			
			$difference = $unixDate - $now;
			$tense = "from now";
			
		}
	
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) 
		{
			
			$difference /= $lengths[$j];
			
		}
	
		$difference = round($difference);
	
		if($difference != 1) 
		{
			
			$periods[$j].= "s";
			
		}
	
		return "$difference $periods[$j] {$tense}";
		
	}

}