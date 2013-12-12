<?php

namespace Core;

abstract class DataModel extends General
{

	public $fields = array('id');
	public $table = 'users';
	public $sql = '';
	public $limit = 10;
	public $offset = 0;
	public $results = array();
    public $resultSetName = 'default';
	
	public function exists()
	{
		
		$db = new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		
		$result = $db->query('
		SELECT COUNT(id)
		FROM '.$this->table.'
		'.$this->sql.'
		LIMIT 1
		');
		
		$row = $result->fetch_row();
		
		return (bool)$row[0];
		
	}

	public function prepare()
	{
		
		$db = new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        $this->sqlQuery = '
		SELECT '.implode(', ', $this->fields).'
		FROM '.$this->table.'
		'.$this->sql.'
		LIMIT '.$this->offset.', '.$this->limit.'
		';
        
		$result = $db->query($this->sqlQuery);
		
		if(!$result)
		{
			
			die($db->error);
			
		}
		
		$results = array();
		$iteration = 0;
		
		if($result->num_rows > 0)
		{
			
			while($row = $result->fetch_object())
			{
			
				foreach($this->fields as $field)
				{
					
					$fieldKey = $field;
					
					if(strstr($fieldKey, ' '))
					{
						
						$fieldKeyArray = explode(' ', $fieldKey);
						$fieldKey = end($fieldKeyArray);
						
					}
				
					$results[$this->resultSetName][$iteration][$fieldKey] = trim($row->$fieldKey);
					
				}
				
				$iteration++;
				
			}
			
		}
		
		$this->results = $results[$this->resultSetName];
		
	}

}