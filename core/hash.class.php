<?php
namespace CORE;
class Hash
{
	private static $protectedFolder;
	public static function init():void
	{
		self::$protectedFolder=root()."/.protected/";
		if(!file_exists(self::$protectedFolder.KEYFILE))
		{
            if(!file_exists(self::$protectedFolder))
			mkdir(self::$protectedFolder);
			$rsa = new \phpseclib\Crypt\RSA();
			extract($rsa->createKey());
			$date=date("Y-m-d H:i:s");
			$file='<?php return array("KEY.PUBLIC"=>"'.$publickey.'","KEY.PRIVATE"=>"'.$privatekey.'","KEY.CREATED.DATE"=>"'.$date.'") ?>';
			file_put_contents(self::$protectedFolder.KEYFILE,$file);
			chmod(self::$protectedFolder,0440);
		}
	}
	// public static function encrypt(string $str):string
	// {
	// 	$cls=new Crypt_RSA();
	// 	return $cls->encrypt($str, self::getPublicKey());
	// }
	private static function getPrivateKey():string
	{
		return (require self::$protectedFolder.KEYFILE)['KEY.PRIVATE'];
	}
	private static function getPublicKey():string
	{
		return (require self::$protectedFolder.KEYFILE)['KEY.PUBLIC'];
	}
	private static function getKeyDate():string
	{
		return require self::$protectedFolder.KEYFILE['KEY.CREATED.DATE'];
	}
	public static function encrypt($value): string
	{
        openssl_public_encrypt($value,$finaltext,self::getPublicKey());
        return base64_encode($finaltext);
	}
	public static function decrypt($encryptedValue): string
	{
		$privkey=self::getPrivateKey();
		openssl_get_privatekey($privkey);
		openssl_private_decrypt(base64_decode($encryptedValue),$finaltext,$privkey);
		return $finaltext ?? '';
    }
	public static function decryptArray(array $array):array
	{
		$privkey=self::getPrivateKey();
		openssl_get_privatekey($privkey);
		foreach($array as $key=>$data)
		{
			openssl_private_decrypt(base64_decode($data),$finaltext,$privkey);
			$array[$key]=$finaltext;
		}
		return $array;
	}
	public static function encryptArray(array $array):array
	{
		$pubkey=self::getPublicKey();
		foreach($array as $key=>$data)
		{
			openssl_public_encrypt($data,$finaltext,$pubkey);
			$array[$key]=base64_encode($finaltext);
		}
		return $array;
	}
    public static function generatePasswordHash(string $password, string $salt) : string
    {
        $password = hash_hmac('sha256', $password, hash('sha1', $salt));
        $options = [
            'cost' => 14
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
    public static function getSalt()
    {
        return bin2hex(random_bytes(32));
    }
    public static function verifyPassword(string $password, string $salt, string $passwordHash) : string
    {
        $password = hash_hmac('sha256', $password, hash('sha1', $salt));
        return password_verify($password, $passwordHash);
    }
    public static function generateSignature(string ...$params) : string
    {
        if(count($params) < 1)
            $params = [rand(), rand(), time()];
        $nonce = rand().time().rand();
        $body = implode(",",$params);
        $hash = hash("sha256", $body);
        for($key=0; $key < 64; $key++)
        {
            if($key%20 == 0 && $key > 0)
                $hash[$key] = "-";
        }
        $nonce = hash("sha256", $nonce);
        for($key=0; $key < 64; $key++)
        {
            if($key%20    == 0 && $key > 0)
                $nonce[$key] = "-";
        }
        $hash .= "-".$nonce;
        $hash = explode("-",$hash);
        $hash = implode("_-_",self::encryptArray($hash));
        $hash = md5(rand().time().rand()) ."_-_". $hash;
        return $hash;
    }
    public static function verifySignature(string $hash, string ...$payload) : bool
    {
        $hash1 = self::generateSignature(implode(",", $payload));
        $hash1 = implode("_-_", self::decryptArray(explode("_-_",$hash1)));
        $hash = implode("_-_", self::decryptArray(explode("_-_",$hash)));
        return self::normalizeSignature($hash1) == self::normalizeSignature($hash);
    }
    private static function normalizeSignature(string $signature) : string
    {
        $signature = explode("_-_", $signature);
        for($i=5; $i<9; $i++)
            unset($signature[$i]);
        return implode("_-_", $signature);
    }
    public static function createAntiForgeryToken() : string
    {
        $nonce = rand().time().rand();
        $payload = self::getUserInfo() . hash("sha256", $nonce);
        return rtrim(base64_encode(self::generateSignature($payload) . "_-+-_" . hash("sha256", $nonce)), "==");
    }
    public static function validateAntiForgeryToken(string $token) : bool
    {
        $originalToken = $token;
        $token.="==";
        $token = base64_decode($token);
        $token = explode("_-+-_", $token);
        $status = self::verifySignature($token[0],  self::getUserInfo() . ($token[1] ?? ''));
        return $status;
    }
    private static function getUserInfo() : string
    {   
        return $_SERVER['HTTP_USER_AGENT'] . getIp() . hash("sha256", $_SERVER['HTTP_USER_AGENT']);
    }
}
