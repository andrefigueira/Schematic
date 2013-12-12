<?php

namespace Core;

class Router extends General
{

	public $viewDir = 'app/Views/';
	public $viewExtension = '.php';
	public $defaultView = 'home';
	public $defaultController = 'Home';

	public function route()
	{

		$requestUri = explode('/', $_SERVER['REQUEST_URI']);
		$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
		 
		for($i= 0; $i < sizeof($scriptName); $i++)
		{

			if($requestUri[$i] == $scriptName[$i])
			{
			
				unset($requestUri[$i]);
				
			}
			
		}
		 
		$this->command = array_values($requestUri);
		$this->dispatch();

	}

	private function dispatch()
	{

		$view = $this->view();
        
		if($view !== null){ require_once $view;}

	}

	private function view()
	{

		$urlPrefix = dirname(dirname(__DIR__)) . '/' . $this->viewDir;

		if(empty($this->command[0]))
		{
		
			return $urlPrefix . $this->defaultView . $this->viewExtension;

		}
		else
		{

			$file = $urlPrefix . $this->command[0] . $this->viewExtension;

            if($this->command[0] == 'ajax')
            {

                if(!isset($this->command[1])){ throw new \Exception('Must set a controller');}
                if(!isset($this->command[2])){ throw new \Exception('Must set a method');}

                $_GET['controller'] = $this->command[1];
                $_GET['method'] = $this->command[2];

            }
            else
            {

                $_GET['controller'] = $this->command[0];

                if(isset($this->command[1])){ $_GET['method'] = $this->command[1];}

            }

			if(file_exists($file))
			{

				return $file;

			}
			else
			{

				$this->trigger404();
				
				return $urlPrefix . '404' . $this->viewExtension;

			}

		}

	}

	private function trigger404()
	{

		header('HTTP/1.1 404 Not Found');

	}

}