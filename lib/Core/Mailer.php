<?php

namespace Core;

class Mailer extends General
{

	public $to;
	public $toName = '';
	public $from = DEFAULT_FROM_EMAIL;
	public $fromName = 'Holla@Me';
	public $html = false;
	public $subject = 'Mailer Email';
	public $excerpt = '';
	public $content;
	public $plainContent = '';
	public $template = HTML_EMAIL_TEMPLATE;
	private $email;
	private $headers;
	
	public function send()
	{
	
		$this->prepare();
		
		$mail = new \PHPMailer();

		$mail->IsSMTP();        
		$mail->Host = 'dhxy-wxdc.accessdomain.com'; 
		$mail->SMTPAuth = true;                             
		$mail->Username = 'info@widezike.com';                         
		$mail->Password = '4282349h14734!sdjX79';  
		$mail->Port = 465;                         
		$mail->SMTPSecure = 'ssl';                        
		
		$mail->From = $this->from;
		$mail->FromName = $this->fromName;
		$mail->AddAddress($this->to, $this->toName);  
		$mail->AddReplyTo($this->from, $this->fromName);
		                              
		$mail->IsHTML($this->html);                                  
		
		$mail->Subject = $this->subject;
		$mail->Body    = $this->email;
		$mail->AltBody = $this->plainContent;
		
		$mail->Send();
		
	}
	
	private function prepare()
	{
		
		if($this->html)
		{
		
			$this->email = file_get_contents($this->template);
			$this->email = str_replace(array('{content}', '{excerpt}'), array($this->content, $this->excerpt), $this->email);
			
		}
		else
		{
			
			$this->email = $this->content;
			
		}
		
	}

}