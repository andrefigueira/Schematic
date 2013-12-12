<?php

namespace Core;

class View extends General
{

	private $html;
	private $regex = '/\{\{(.*?)\}\}/';
	public $templatesDir = 'templates';
	public $extension = 'html';
	
	private function process($data)
	{
		
		$this->html = $this->template;
	
		preg_match_all($this->regex, $this->html, $matches);
		
		foreach($matches[1] as $key => $value)
		{
		
			$this->dataType = '';
			$subInfo = false;
			
			if(strstr($value, '.'))
			{
				
				$split = explode('.', $value);
				$subInfo = true;
				
			}
			
			if(is_array($data))
			{
			
				if($subInfo)
				{
				
					$info = $data[$split[0]][$split[1]];
					
				}
				else
				{
				
					$info = $data[$value];
					
				}
				
			}
			elseif(is_object($data))
			{
			
				if($subInfo)
				{
				
					$info = $data->$split[0]->$split[1];
					
				}
				else
				{
				
					$info = $data->$value;
					
				}
				
			}
			else
			{
				
				die('Invalid data type');
			
			}
			
			$this->html = str_replace('{{'.$value.'}}', $info, $this->html);
			
		}
		
	}
	
	public function template($file)
	{
		
		try 
		{
		
			$file = dirname(dirname(dirname(__FILE__))) . '/' . $this->templatesDir . '/' . $file . '.' . $this->extension;
			
			$this->template = $this->getFile($file);
			
		}
		catch(Exception $e)
		{
			
			die($e->getMessage());
			
		}
		
	}

	public function parse($data = array())
	{
		
		$this->process($data);
		
		return $this->html;
		
	}

}