<?php
namespace CORE;
class Validator
{
	public const GET='get';
	public const POST='post';
	public static function validate(string $method='get',array ...$ar):bool
	{
		if($method=='get')
		$data=GET;
		else if($method=='post')
		$data=POST;
		else
		throw new Exception('Invalid Method. Should be either <strong>get</strong> or <strong>post</strong>.');
		$fld=[];
		foreach($ar as $field)
		{
			$fld=$field;
			$st=true;
			$val=$data[$field['name']]??NULL;
			if($val==NULL)
			$st=false;
			switch($field['type'])
			{
				case 'same':
					if($data[$field['match']] == $data[$field['against']])
					$st=true;
				break;
				case 'email':
					$st=self::isEmail($val);
				break;
				case 'name':
					$st=self::isName($val);
				break;
				case 'url':
					$st=self::isURL($val);
				break;
				case 'domain':
					$st=self::isDomain($val);
				break;
				case 'int':
					$ln=intval($val);
					if($ln<(int)($field['min']??1) && $ln>(int)($field['max']??99999999999999999999))
					$st=false;
				break;
				case 'array':
					$ln=count($ln);
					if($ln<(int)($field['min']??1) && $ln>(int)($field['max']??99999999999999999999))
					$st=false;
				break;
				default:
					$ln=strlen($val);
					if($ln<(int)($field['min']??1) && $ln>(int)($field['max']??99999999999999999999))
					{
						$st=false;
					}
				break;
			}
			if($st==false)
			{
				throw new Exception($fld['errorMessage']??'The '.($fld['label']??$fld['name']).' is not valid.');
				return false;
			}
		}
		return true;
	}
    public static function validateField(string $fieldName, array $rules, $val) : \stdClass
    {
        $result = new \stdClass();
        $result->status=true;
        $result->message="";
        if(isset($rules['Required']))
        {
            if($rules['Required']==true)
            {
                if((empty($val) && $val != 0) || (is_array($val) ? (count($val) < 1) : strlen(str_replace(" ","",$val))<1))
                {
                    $result->status=false;
                    $result->message=$fieldName." is required";
                    return $result;
                }
            }
        }
        foreach($rules as $key=>$data)
        {
            switch($key)
            {
				case 'Required':
					if($data === true)
					{
						if((empty($val) && $val != 0) || (is_array($val) ? (count($val) < 1) : strlen(str_replace(" ","",$val))<1))
						{
							$result->status=false;
							$result->message=$fieldName." is required";
						}
					}
                    break;
                case 'Max':
                    if((($rules['Type'] ?? 'String') == 'Int' && $val > $data) || (($rules['Type'] ?? 'String') != 'Int' && strlen($val) > $data))
                    {
                        $result->status = false;
                        if(($rules['Type'] ?? 'String') == 'Int')
                        {
                            $result->message="Maximum value for $fieldName is ".$data;
                        }
                        else
                        {
                            $result->message="Maximum length for $fieldName is ".$data;
                        }
                    }
                    break;
                case 'Min':
                    if((($rules['Type'] ?? 'String') == 'Int' && $val<$data) || (($rules['Type'] ?? 'String') != 'Int' && strlen($val)<$data))
                    {
                        $result->status=false;
                        if(($rules['Type'] ?? 'String') == 'Int')
                        {
                            $result->message="Minimum value for $fieldName is ".$data;
                        }
                        else
                        {
                            $result->message="Minimum length for $fieldName is ".$data;
                        }
                    }
                    break;
                case 'Type':
                    switch($data)
                    {
                        case 'Email':
                            if(!self::isEmail($val))
                            {
                                $result->status=false;
                                $result->message="Email is not valid";
                            }
                            break;
                        case 'Url':
                            if(!self::isURL($val))
                            {
                                $result->status=false;
                                $result->message="Url is not valid";
                            }
                            break;
                        case 'Domain':
                            if(!self::isDomain($val))
                            {
                                $result->status=false;
                                $result->message="Domain is not valid";
                            }
                            break;
                        case 'Int':
                            if(!self::isNumber($val))
                            {
                                $result->status=false;
                                $result->message="Number is not valid";
                            }
                            break;
                        case 'Name':
                            if(!self::isName($val))
                            {
                                $result->status=false;
                                $result->message="Invalid $fieldName";
                            }
                            break;
                    }
                    break;
            }
            if($result->status==false)
                break;
        }
        return $result;
    }
	public static function isSubmit(string $method='get',string ...$ar):bool
	{
		if($method=='get')
		$data=GET;
		else if($method=='post')
		$data=POST;
		else
		throw new Exception('Invalid Method. Should be Either <strong>get</strong> or <strong>post</strong>.');
		$st=true;
		foreach($ar as $field)
		{
			if(is_array($data[$field]??NULL))
			{
				if(strlen(str_replace(' ','',implode('',$data[$field])))<1)
				{
					$st=false;
					break;
				}
			}
			else if(strlen($data[$field]??'')<1)
			{
				$st=false;
				break;
			}
		}
		return $st;
	}
	public static function isEmail(string $var):bool
	{
		return filter_var($var, FILTER_VALIDATE_EMAIL);
	}
	public static function isURL(string $var):bool
	{
		return filter_var($var, FILTER_VALIDATE_URL);
	}
    public static function isDomain(string $var):bool
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $var)
            && preg_match("/^.{1,253}$/", $var)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $var)   );
    }
	public static function isName(string $var):bool
	{
		return preg_match("/^[a-zA-Z ]*$/",$var);
	}
	public static function isStrongPassword(string $var):bool
	{
		return !preg_match("'A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])[-_a-zA-Z0-9]{6,}z'",$var);
	}
	public static function maskValidate(string $var):bool
	{
		return !preg_match("/^d{1}-d{3}-d{3}-d{4}$/", $var);
	}
	public static function validDate(string $var):bool
	{
		return preg_match("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/", $var);
	}
	public static function isNumber(string $var):bool
	{
		return preg_match('/^[0-9]+$/', $var);
	}
}
