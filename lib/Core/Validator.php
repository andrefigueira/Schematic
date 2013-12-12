<?php

namespace Core;

class Validator
{
    
    //The message is used for defining the responses when a validation fails
    public $message = '';
    
    //params are the validation params, these are predefined in the class
    public $params = array();
    
    public $subject = '';
    
    /**
     * 
     * Checks to see if the subject is an empty string
     * 
     * @return boolean
     * 
     */    
    public function isEmpty()
    {
        
        return (bool)($this->subject == '');
        
    }
    
    /**
     * 
     * Checks to see if the subject is a numeric value
     * 
     * @return boolean
     * 
     */
    public function isNumeric()
    {
        
        return is_numeric($this->subject);
        
    }
    
    /**
     * 
     * Checks to see if the subject is a valid string
     * 
     * @return boolean
     * 
     */
    public function isString()
    {
        
        return is_string($this->subject);
        
    }
    
    public function isEmail()
    {
        
        return filter_var($this->subject, FILTER_VALIDATE_EMAIL);
        
    }
    
    /**
     * 
     * The script which kicks off the validation, first it validates the validation parameters
     * passed by the developer, then loops through passed parameters to check if the passed variables
     * are valid within the defined parameters
     * 
     * @throws Exception
     * 
     * @return boolean
     * 
     */
    public function validate()
    {
        
        $this->validateParams();
        
        switch($this->params['type'])
        {
            
            case 'numeric':
                
                if($this->isNumeric())
                { 
                    
                    return $this->numericValidation();
                
                }
                else
                { 
                    
                    $this->setMessage('Subject (' . $this->subject . ') is not numeric');
                    
                    return false;
                
                }
                
                break;
            
            case 'string':
                
                if($this->isString())
                { 
                    
                    return $this->stringValidation();
                
                }
                else
                { 
                    
                    $this->setMessage('Subject (' . $this->subject . ') is not a string');
                    
                    return false;
                
                }
                
                break;
            
            case 'email':
                
                if($this->isString())
                { 
                    
                    if(!$this->isEmail()){ $this->setMessage('Subject (' . $this->subject . ') is not a valid email');}
                    
                    return $this->isEmail();
                
                }
                else
                { 
                    
                    $this->setMessage('Subject (' . $this->subject . ') is not a string');
                    
                    return false;
                
                }
                
                break;
            
            default:
                throw new Exception('Invalid validation type');
            
        }
        
    }
    
    /**
     * 
     * The numeric validation method, this method is called depending on the validation type defined by
     * the developer, all numeric validation options must be defined here
     * 
     * @throws Exception
     * 
     * @return boolean
     * 
     */
    private function numericValidation()
    {
        
        $this->results = array();

        foreach($this->params as $rule => $value)
        {

            if($rule != 'type')
            {

                switch($rule)
                {

                    case 'min':
                        $this->results['min'] = $this->minValidation($value);
                        break;

                    case 'max':
                        $this->results['max'] = $this->maxValidation($value);
                        break;

                    default:
                        throw new Exception('Rule (' . $rule . ') is not defined within validation class');

                }

            }

        }

        return $this->verifyRulesPassed();
        
    }
    
    /**
     * 
     * The string validation method, this method is called depending on the validation type defined by
     * the developer, all string validation options must be defined here
     * 
     * @throws Exception
     * 
     * @return boolean
     * 
     */
    private function stringValidation()
    {
        
        $this->results = array();
        
        foreach($this->params as $rule => $value)
        {
            
            if($rule != 'type')
            {
                
                switch($rule)
                {
                    
                    case 'minLen':
                        $this->results['minLen'] = $this->minLenValidation($value);
                        break;
                    
                    case 'maxLen':
                        $this->results['maxLen'] = $this->maxLenValidation($value);
                        break;
                    
                    default:
                        throw new Exception('Rule (' . $rule . ') is not defined within validation class');
                    
                }
                
            }
            
        }
        
        return $this->verifyRulesPassed();
        
    }
    
    /**
     * 
     * This method runs through the results array created in the validation methods
     * for the respective validation types and defines if a subject has met the defined criteria to 
     * be validated, will return false if any products fails, validation is only true on all validation parameters having passed
     * 
     * @return boolean
     * 
     */
    private function verifyRulesPassed()
    {
        
        foreach($this->results as $rule => $result)
        {
            
            if(!$result)
            {
                
                if($this->message != ''){  $this->setMessage('Failed validation (' . $rule . ') did not conform to parameters');}
                
                return false;
                
                break;
                
            }
            
        }
        
        return true;
        
    }
    
    /**
     * 
     * Checks if the subject is at least of the same value as the defined minimum value
     * 
     * @param int $minLen
     * 
     * @return boolean
     * 
     */
    private function minValidation($min)
    {
        
        return (bool)($this->subject >= $min);            
        
    }
    
    /**
     * 
     * Checks if the subject is at most of the same value as the defined maximum value
     * 
     * @param int $minLen
     * 
     * @return boolean
     * 
     */
    private function maxValidation($max)
    {
        
        return (bool)($this->subject <= $max);            
        
    }
    
    /**
     * 
     * Checks if the subject is at least of the same value as the defined minimum value
     * 
     * @param int $minLen
     * 
     * @return boolean
     * 
     */
    private function minLenValidation($minLen)
    {
        
        return (bool)(strlen($this->subject) >= $minLen);            
        
    }
    
    /**
     * 
     * Checks if the subject is at most of the same value as the defined maximum value
     * 
     * @param int $minLen
     * 
     * @return boolean
     * 
     */
    private function maxLenValidation($maxLen)
    {
        
        return (bool)(strlen($this->subject) <= $maxLen);            
        
    }
    
    /**
     * 
     * Validations the defined options, only runs the validations if all passed rules are valid
     * 
     * @throws Exception
     * 
     */
    private function validateParams()
    {
        
        if(!array_key_exists('type', $this->params)){ throw new Exception('You must set the type of parameter to validate');}
        
        $this->validateParamsOptions();
        
    }
    
    /**
     * 
     * Checks the type which has been defined for validation, if it's not defined here with it's options
     * it will not be allowed to run any kind of validation
     * 
     * @param type $optionType
     * 
     * @throws Exception
     * 
     * @return array
     * 
     */
    private function validationOptions($optionType)
    {
        
        switch($optionType)
        {
            
            case 'numeric': 
                
                return array(
                    'min',
                    'max'
                );
                
                break;
            
            case 'string': 
                
                return array(
                    'minLen',
                    'maxLen'
                );
                
                break;
            
            case 'email': 
                
                return array();
                
                break;
        
            default:
                throw new Exception('Invalid validation type (' . $optionType . ') in validateParamsOptions()');
            
        }
        
    }
    
    /**
     * 
     * Validates the validation options which have been passed to ensure that they can be run
     * 
     * @throws Exception
     * 
     */
    private function validateParamsOptions()
    {
        
        $this->validationOptions($this->params['type']);
        
    }
    
    /**
     * 
     * Fetch any set string if it exists, can be used for notifications
     * 
     * @return string
     * 
     */
    public function getMessage()
    {
        
        return $this->message;
        
    }
    
    private function setMessage($message)
    {
        
        if(isset($this->onErrorMsg))
        {
            
            $this->message = $this->onErrorMsg;
            
        }
        else
        {
            
            $this->message = $message;
            
        }
        
    }
    
}