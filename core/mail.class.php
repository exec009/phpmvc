<?php
namespace CORE;
use \PHPMailer;
use Exception;
class Mail
{
	private $subject, $to, $from, $message;
	private $mail;
	public static function init($mail='DEFAULT') : self
	{
		if(!isset(MAIL[$mail]))
		throw new \Exception('Requested Mail '.$mail.' Doesn"t Exist');
		$ml=new self();
		$ml->mail = new PHPMailer();
		$ml->mail->Username = isset(MAIL[$mail]['username']) ? MAIL[$mail]['username'] : MAIL[$mail]['email'];
		$ml->mail->From = MAIL[$mail]['email'];
		$ml->mail->Password = MAIL[$mail]['password'];
		$ml->mail->FromName = MAIL[$mail]['name'];
		$ml->mail->Host = MAIL[$mail]['host'];
		$ml->mail->IsHTML(true);
		if(isset(MAIL[$mail]['port']))
		$ml->mail->Port = MAIL[$mail]['port'];
		if(isset(MAIL[$mail]['crypto']))
		$ml->mail->SMTPSecure = MAIL[$mail]['crypto'];
		if(MAIL[$mail]['smtp'])
		{
			$ml->mail->IsSMTP();
			$ml->mail->SMTPAuth = true;
		}
		return $ml;
	}
    public function debugOn()
    {
        $this->mail->SMTPDebug=2;
    }
	public function setSubject(string $var) : self
	{
		$this->mail->Subject=$var;
        return $this;
	}
	public function setBody(string $var) : self
	{
		$this->mail->Body=$var;
        return $this;
	}
	public function setTemplate(/*\CORE\MVC\Controller */$mailController, string $action, array $params = []) : self
	{
        $cntrl=explode("\\",get_class($mailController));
        $cntrl=$cntrl[count($cntrl)-1];
        $mailController->area='mail';
        $mailController->controller=str_replace("controller","",strtolower($cntrl));
        $mailController->action=$action;
        $mailController->beforeRender();
        $action = count($params) > 0 ? $mailController->{$action}($params) : $mailController->{$action}();
        $mailController->afterRender();
		$this->mail->Body = $action->getHtml();
        return $this;
	}
	public function addAddress(string ...$email) : self
	{
		foreach($email as $data)
		$this->mail->AddAddress($data);
        return $this;
	}
	public function send() : bool
	{
		if($this->mail->Send())
		{
			$this->mail->ClearAllRecipients();
			$this->mail->ClearCCs();
			$this->mail->ClearBCCs();
			$this->setSubject("");
			$this->setBody("");
			return true;
		}
		else
		{
			if(\CORE\Debug::isOn())
			throw new Exception("Mailer Error: " . $this->mail->ErrorInfo);
			else
			throw new Exception('There is an error in sending email. Please tryagain after sometime.');
			return false;
		}
	}
}
/*
$mail=new Mail();
$mail->setTemplate(\CONTROLLERS\MAIL\MailController::init(), 'forgetPass')
*
*/