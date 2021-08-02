<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
require APPPATH . '/libraries/SMTP/Exception.php';
require APPPATH . '/libraries/SMTP/PHPMailer.php';
require APPPATH . '/libraries/SMTP/SMTP.php';
date_default_timezone_set('Asia/Jakarta');

class Token extends REST_Controller
{
	public function __construct($config = 'rest')
	{
		parent::__construct($config);
		$this->load->database();
		$this->load->helper('form', 'url');
		//$this->load->helper('email');
		//header('Content-Type: application/json; charset=UTF-8');
	}
    
	public function index_get()
	{
        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>405, 'code'=>'METHOD_NOT_ALLOWED', 'status'=>false, 'message'=>'Method Not Allowed', 'data'=>[]), 405, 'application/json; charset=UTF-8');

        exit();
    }
    
    public function index_post()
	{
        $getXForwardedFor = $this->input->get_request_header('X-Forwarded-For', TRUE);
        $rawData = json_decode($this->input->raw_input_stream, true);
        $getApiKey = $this->input->get_request_header('X-API-Key', TRUE);
        $getContentType = explode(';', $this->input->get_request_header('Content-Type', TRUE));
        $getAuth = $this->input->get_request_header('Authorization', TRUE);
        $getAppID = $this->input->get_request_header('X-App-ID', TRUE);
        $getAppVersion = $this->input->get_request_header('X-App-Version', TRUE);
        $getPlatform = $this->input->get_request_header('X-Platform', TRUE);

        if(!empty($getXForwardedFor))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>403, 'code'=>'REQUEST_REJECTED', 'status'=>false, 'message'=>'Hack Attempt', 'data'=>[]), 403, 'application/json; charset=UTF-8');

            exit();
        }

        if(!in_array('application/json', $getContentType))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>404, 'code'=>'NOT_FOUND', 'status'=>false, 'message'=>'Not Found', 'data'=>[]), 404, 'application/json; charset=UTF-8');

            exit();
        }

        $this->validateAuth();

        if(empty(trim($getAuth)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Basic realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'No Auth in the Authorization Request Header', 'data'=>[]), 401, 'application/json; charset=UTF-8');
            
            exit();
        }

        $getAuthFix = "";

        if(!empty($getAuth)) // CHECK BASIC TOKEN
        {
            if(preg_match('/Basic\s(\S+)/', $getAuth, $matches))
            {
                $getAuthFix = $matches[1];

                // CHECK BASIC AUTH
                $getBasicAuth = explode(":", base64_decode($getAuthFix));

                $checkAuth = $this->db->query("SELECT * FROM app_data WHERE BINARY basicUsername = ? AND BINARY basicPassword  = ? AND BINARY xApiKey = ?", array($getBasicAuth[0], $getBasicAuth[1], $getApiKey));
                $resultCheckAuth = $checkAuth->result_array();

                if(count($resultCheckAuth) <= 0)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Basic realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'Unauthorized', 'data'=>[]), 401, 'application/json; charset=UTF-8');

                    exit();
                }

                if(count($resultCheckAuth) > 0)
                {
                    // IZINKAN
                }
            }
        }

        if(empty(trim($getAuthFix)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Basic realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'Unauthorized', 'data'=>[]), 401, 'application/json; charset=UTF-8');
            
            exit();
        }

        //

        $getGrantType = htmlentities(trim($rawData['grant_type']), ENT_QUOTES, 'UTF-8');
        $getClientID = htmlentities(trim($rawData['client_id']), ENT_QUOTES, 'UTF-8');
        $getClientSecret = htmlentities(trim($rawData['client_secret']), ENT_QUOTES, 'UTF-8');
        $getAccount = htmlentities(trim($rawData['account']), ENT_QUOTES, 'UTF-8');
        $getScopes = htmlentities(trim($rawData['scopes']), ENT_QUOTES, 'UTF-8');
        $getChallengeToken = htmlentities(trim($rawData['data']['challengeToken']), ENT_QUOTES, 'UTF-8');
        $getCredentials = htmlentities(trim($rawData['data']['credentials']), ENT_QUOTES, 'UTF-8');

        //echo $getChallengeToken; exit();

        $validateVersion = $this->db->query("SELECT * FROM app_data WHERE BINARY xAppVersion = ? AND BINARY XPlatform = ? AND BINARY appReqId = ?", array($getAppVersion, $getPlatform, $getAppID));
        $resultValidateVersion = $validateVersion->result_array();

        if(count($resultValidateVersion) <= 0)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Versi atau platform aplikasi yang Anda gunakan tidak tersedia!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(count($resultValidateVersion) > 0)
        {
            foreach($resultValidateVersion as $rowAppVersion)
            {
                $isUpdate = intval($rowAppVersion['isUpdate']);
                $updateLink = $rowAppVersion['updateLink'];
                $updateNotes = $rowAppVersion['updateNotes'];
                $clientIDFix = $rowAppVersion['clientId'];
                $clientSecretFix = $rowAppVersion['clientSecret'];
            }

            if($isUpdate == 1)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'UPDATE_NEEDED', 'status'=>false, 'message'=>'Update Diperlukan', 'data'=>[
                    'isUpdate'=>$isUpdate,
                    'updateLink'=>$updateLink,
                    'updateNotes'=>$updateNotes
                ]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            if($isUpdate != 1)
            {
                // VALIDATE CLIENT ID AND CLIENT SECRET
                if($clientIDFix != $getClientID)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'INVALID_CLIENT_ID', 'status'=>false, 'message'=>'Invalid Client ID', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                if($clientSecretFix != $getClientSecret)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'INVALID_CLIENT_SECRET', 'status'=>false, 'message'=>'Invalid Client Secret', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                // LANJUT

                if($getGrantType != "otp" && $getGrantType != "password")
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'INVALID_GRANT_TYPE', 'status'=>false, 'message'=>'Invalid Grant Type', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                // VALIDASI CHALLENGE TOKEN
                $hashChallengeToken = hash('sha256', $getChallengeToken . $this->config->item('saltKeyChallengeToken'));
                $validateChallengeToken = $this->db->query("SELECT * FROM auth_data WHERE BINARY challengeToken = ? AND BINARY grantType = ? AND accessToken = ''", array($hashChallengeToken, $getGrantType));
                $resultValidateChallengeToken = $validateChallengeToken->result_array();

                if(count($resultValidateChallengeToken) <= 0)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Permintaan login Anda tidak diterima. Silakan ulangi dari awal!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
                }

                if(count($resultValidateChallengeToken) > 0)
                {
                    foreach($resultValidateChallengeToken as $rowChallengeToken)
                    {
                        $getUsernamenya = $rowChallengeToken['username'];
                        $tempOTP = $rowChallengeToken['tempOTP'];
                        $grantType = $rowChallengeToken['grantType'];
                        $challengeTokenExpiresAt = intval($rowChallengeToken['challengeTokenExpiresAt']);
                    }

                    // GET USER DETAILS
                    $userDetails = $this->db->query("SELECT id, username, email, namaLengkap, privilege, password FROM users_data WHERE BINARY username = ? AND BINARY privilege = ?", array($getUsernamenya, $getScopes));
                    $resultUserDetails = $userDetails->result_array();

                    if(count($resultUserDetails) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Pengguna tidak ditemukan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if(count($resultUserDetails) > 0)
                    {
                        foreach($resultUserDetails as $rowUserDetails)
                        {
                            $userNo = intval($rowUserDetails['id']);
                            $emailFix = htmlentities(trim($rowUserDetails['email']), ENT_QUOTES, 'UTF-8');
                            $getNamaLengkap = htmlentities(trim($rowUserDetails['namaLengkap']), ENT_QUOTES, 'UTF-8');
                            $privilege = $rowUserDetails['privilege'];
                            $getPasswordHash = $rowUserDetails['password'];
                        }

                        if($grantType == "otp")
                        {
                            $timenya = time();
                            $selisihTime = $challengeTokenExpiresAt - $timenya;

                            if($selisihTime <= 0)
                            {
                                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Sesi telah berakhir, silakan ulangi dari awal!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                exit();
                            }

                            $hashCredentials = hash('sha256', $getCredentials . $this->config->item('saltKeyOTP'));

                            // VALIDATE CREDENTIALS

                            if($tempOTP != $hashCredentials)
                            {
                                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Kode OTP tidak valid', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                exit();
                            }

                            if($tempOTP == $hashCredentials)
                            {
                                $setExpiredInSeconds = 604800;
                                $accessTokenTimestamp = time() + $setExpiredInSeconds;
                                $jwtAccessToken = $this->generateJWT($getUsernamenya, $getNamaLengkap, $emailFix, $clientIDFix, $grantType, $privilege, $userNo, $accessTokenTimestamp);
                                $jwtAccessTokenHash = hash('sha256', $jwtAccessToken . $this->config->item('saltHash1'));
                                $refreshToken = $this->guidv4();
                                $refreshTokenHash = hash('sha256', $refreshToken . $this->config->item('saltHash1'));
                                $jti = $this->guidv4();
                                $ssoToken = $this->guidv4();
                                $ssoTokenHash = hash('sha256', $ssoToken . $this->config->item('saltHash1'));
                                //$guidv4 = $this->guidv4();

                                $lastIPLogin = $_SERVER['REMOTE_ADDR'];
                                $tokenType = "Bearer";

                                // UPDATE AUTH DB
                                $updateAuthData = $this->db->query("UPDATE auth_data SET accessToken = ?, refreshToken = ?, lastIPLogin = ?, accessTokenTimestamp = ?, tokenType = ?, jti = ?, ssoToken = ? WHERE BINARY username = ? AND BINARY challengeToken = ?", array($jwtAccessTokenHash, $refreshTokenHash, $lastIPLogin, $accessTokenTimestamp, $tokenType, $jti, $ssoTokenHash, $getUsernamenya, $hashChallengeToken));
                                
                                if($updateAuthData)
                                {
                                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Login Sukses', 'data'=>[
                                        'access_token'=>$jwtAccessToken,
                                        'refresh_token'=>$refreshToken,
                                        'sso_token'=>$ssoToken,
                                        'expires_in'=>intval($setExpiredInSeconds),
                                        'token_type'=>$tokenType
                                    ]), 200, 'application/json; charset=UTF-8');

                                    exit();
                                }

                                else
                                {
                                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat login, silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                    exit();
                                }

                                exit();
                            }
                        }

                        if($grantType == "password")
                        {
                            $timenya = time();
                            $selisihTime = $challengeTokenExpiresAt - $timenya;

                            if($selisihTime <= 0)
                            {
                                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Sesi telah berakhir, silakan ulangi dari awal!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                exit();
                            }

                            $hashCredentials = hash('sha256', $getCredentials . $this->config->item('saltHash1'));

                            // VALIDATE CREDENTIALS

                            if($getPasswordHash != $hashCredentials)
                            {
                                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Password tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                exit();
                            }

                            if($getPasswordHash == $hashCredentials)
                            {
                                $setExpiredInSeconds = 604800;
                                $accessTokenTimestamp = time() + $setExpiredInSeconds;
                                $jwtAccessToken = $this->generateJWT($getUsernamenya, $getNamaLengkap, $emailFix, $clientIDFix, $grantType, $privilege, $userNo, $accessTokenTimestamp);
                                $jwtAccessTokenHash = hash('sha256', $jwtAccessToken . $this->config->item('saltHash1'));
                                $refreshToken = $this->guidv4();
                                $refreshTokenHash = hash('sha256', $refreshToken . $this->config->item('saltHash1'));
                                $jti = $this->guidv4();
                                $ssoToken = $this->guidv4();
                                $ssoTokenHash = hash('sha256', $ssoToken . $this->config->item('saltHash1'));
                                //$guidv4 = $this->guidv4();

                                $lastIPLogin = $_SERVER['REMOTE_ADDR'];
                                $tokenType = "Bearer";

                                // UPDATE AUTH DB
                                $updateAuthData = $this->db->query("UPDATE auth_data SET accessToken = ?, refreshToken = ?, lastIPLogin = ?, accessTokenTimestamp = ?, tokenType = ?, jti = ?, ssoToken = ? WHERE BINARY username = ? AND BINARY challengeToken = ?", array($jwtAccessTokenHash, $refreshTokenHash, $lastIPLogin, $accessTokenTimestamp, $tokenType, $jti, $ssoTokenHash, $getUsernamenya, $hashChallengeToken));
                                
                                if($updateAuthData)
                                {
                                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Login Sukses', 'data'=>[
                                        'access_token'=>$jwtAccessToken,
                                        'refresh_token'=>$refreshToken,
                                        'sso_token'=>$ssoToken,
                                        'expires_in'=>intval($setExpiredInSeconds),
                                        'token_type'=>$tokenType
                                    ]), 200, 'application/json; charset=UTF-8');

                                    exit();
                                }

                                else
                                {
                                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat login, silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                    exit();
                                }

                                exit();
                            }
                        }
                    }
                }

                else

                exit();
            }
        }

		exit();
	}

	public function index_delete()
	{
        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>405, 'code'=>'METHOD_NOT_ALLOWED', 'status'=>false, 'message'=>'Method Not Allowed', 'data'=>[]), 405, 'application/json; charset=UTF-8');
        
		exit();
    }
    
    public function index_put()
	{
        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>405, 'code'=>'METHOD_NOT_ALLOWED', 'status'=>false, 'message'=>'Method Not Allowed', 'data'=>[]), 405, 'application/json; charset=UTF-8');
        
		exit();
	}

	public function index_patch()
	{
        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>405, 'code'=>'METHOD_NOT_ALLOWED', 'status'=>false, 'message'=>'Method Not Allowed', 'data'=>[]), 405, 'application/json; charset=UTF-8');

		exit();
	}

	public function validateAuth()
	{
		$apiKeyHeader = $this->input->get_request_header('X-API-Key');

		if(preg_match('/[A-Z]/', $apiKeyHeader))
		{
			echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>403, 'code'=>'INVALID_API_KEY', 'status'=>false, 'message'=>'Forbidden', 'data'=>[]), 403, 'application/json; charset=UTF-8');
            
			exit();
		}

		$detectAuth = $this->output->get_output();
		$decodeDetectAuth = json_decode($detectAuth, true);
		
		$authCode = isset($decodeDetectAuth['error']);

		if($authCode == "Invalid API key ")
		{
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>403, 'code'=>'INVALID_API_KEY', 'status'=>false, 'message'=>'Forbidden', 'data'=>[]), 403, 'application/json; charset=UTF-8');

			exit();
		}

		if($authCode == "Unauthorized")
		{            
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Bearer realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'Unauthorized', 'data'=>[]), 401, 'application/json; charset=UTF-8');

            exit();
		}
    }

    public function showJson($arrayHeader, $arrayBody, $responseCode, $mime)
    {
        $getArrayHeader = $arrayHeader;

        for($loopHeader = 0; $loopHeader < sizeof($getArrayHeader); $loopHeader++)
        {
            if(!empty($getArrayHeader[$loopHeader]))
            {
                header($getArrayHeader[$loopHeader]);
            }
        }
        
        $this->output->set_status_header(intval($responseCode));
        return json_encode($arrayBody, JSON_UNESCAPED_SLASHES);
    }

    public function sensorEmail($dataEmailnya)
    {
        $dataEmail = $dataEmailnya;

        $em   = explode("@", $dataEmail);
        $name = implode('@', array_slice($em, 0, count($em)-1));
        $len  = floor(strlen($name) / 2);

        return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);
    }

    public function generateOTPCode()
    {
        $length = 6;
        $characters = '01234567890123456789';
        $joinOTPCode = '';

        for ($i = 0; $i < $length; $i++)
        {
            $joinOTPCode .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        $joinOTPCode = str_shuffle(strtoupper($joinOTPCode));

        return $joinOTPCode;
    }

    public function doSendOTPCode($otpCodenya, $emailUsernya, $getUsernamenya, $getNamanya)
	{
		$otpCode = $otpCodenya;
		$getEmailUser = htmlentities($emailUsernya, ENT_QUOTES, 'UTF-8');
		$getUsernameUser = htmlentities($getUsernamenya, ENT_QUOTES, 'UTF-8');
        $getNama = htmlentities($getNamanya, ENT_QUOTES, 'UTF-8');
        $timeExpiredOTPTimestamp = time() + 300;
        $timeExpiredOTPDate = date("Y-m-d H:i:s", $timeExpiredOTPTimestamp) . " WIB";

		$mail = new PHPMailer();
		$mail->IsSMTP();

		//var_dump($getEmailUser);

		$mail->SMTPDebug  = 0;
		$mail->SMTPAuth   = TRUE;
		$mail->SMTPSecure = $this->config->item('MethodSmtpNoReply1');
		$mail->Port       = $this->config->item('PortSmtpNoReply1');
		$mail->Host       = $this->config->item('HostSmtpNoReply1');
		$mail->Username   = $this->config->item('UsernameSmtpNoReply1');
		$mail->Password   = $this->config->item('PasswordSmtpNoReply1');
		$mail->CharSet = $this->config->item('CharsetSmtpNoReply1');

		$mail->IsHTML(true);
		$mail->AddAddress($getEmailUser, $getUsernameUser);
		$mail->SetFrom($this->config->item('UsernameSmtpNoReply1'), "RumahinApp");
		$mail->Subject = "Kode OTP Untuk Login Ke RumahinApp";
		$content = 'Halo, <b>' . $getNama . '</b>.<br><br>Kode OTP untuk masuk ke aplikasi RumahinApp: <b>' . $otpCode . '</b>. Kode ini bersifat RAHASIA, jangan berikan kepada siapapun termasuk yang mengatasnamakan kami! Berlaku hingga <b>' . $timeExpiredOTPDate . '</b> atau <b>SEKALI PAKAI SAJA</b><br><br>( Pesan ini adalah pesan otomatis yang di-generate oleh sistem. Jangan membalas pesan ini! )';

		$mail->MsgHTML($content);
		
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		//var_dump($mail);
		
		if(!$mail->Send())
		{
			//return false;
		}

		else
		{
			//return true;
		}
    }
    
    public function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function generateJWT($getUsernamenya, $getNamaLengkap, $getEmailUser, $clientIDFix, $grantType, $getScopes, $sub, $accessTokenTimestamp)
    {
        $kunciRahasia = "bHqyHDkaSFtp75NHU7a4gc8ANzeKVHYD";

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $iat = time();
        $exp = $accessTokenTimestamp;

        $payload = [
            'grantType'=>$grantType,
            'accountType'=>$getScopes,
            'username'=>$getUsernamenya,
            'email'=>$getEmailUser,
            'emailVerified'=>true,
            'name'=>$getNamaLengkap,
            'sub'=>strval($sub),
            'iss'=>base_url(),
            'aud'=>$clientIDFix,
            'iat'=>$iat,
            'exp'=>$exp,
            'jti'=>$this->guidv4(),
            'scopes'=>$getScopes
        ];

        $payload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $base64UrlHeader = str_replace(['+','/','='], ['-','_',''], base64_encode($header));
        $base64UrlPayload = str_replace(['+','/','='], ['-','_',''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $kunciRahasia, true);
        $base64UrlSignature = str_replace(['+','/','='], ['-','_',''], base64_encode($signature));
        $JWT = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

        return $JWT;
    }
}