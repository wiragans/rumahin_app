<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class RefreshToken extends REST_Controller
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

        $this->validateAppVersionData($getAppID, $getAppVersion, $getPlatform);
        $this->validateAuth();

        if(empty(trim($getAuth)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Bearer realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'No Auth in the Authorization Request Header', 'data'=>[]), 401, 'application/json; charset=UTF-8');
            
            exit();
        }

        $getAuthFix = "";

        if(!empty($getAuth)) // CHECK BEARER TOKEN
        {
            if(preg_match('/Bearer\s(\S+)/', $getAuth, $matches))
            {
                $getAuthFix = $matches[1];

                // IZINKAN
            }
        }

        if(empty(trim($getAuthFix)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Bearer realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'Unauthorized', 'data'=>[]), 401, 'application/json; charset=UTF-8');
            
            exit();
        }

        $bearerAccessToken = $getAuthFix;
        $hashBearerAccessToken = hash('sha256', $bearerAccessToken . $this->config->item('saltHash1'));
        
        // VALIDATE BEARER TOKEN
        $validasiAuth = $this->db->query("SELECT * FROM auth_data WHERE BINARY accessToken = ?", array($hashBearerAccessToken));
        $resultValidasiAuth = $validasiAuth->result_array();

        if(count($resultValidasiAuth) <= 0)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Bearer realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'Unauthorized', 'data'=>[]), 401, 'application/json; charset=UTF-8');

            exit();
        }

        if(count($resultValidasiAuth) > 0)
        {
            foreach($resultValidasiAuth as $rowAuth)
            {
                $getFixUsername = $rowAuth['username'];
                $accessTokenTimestamp = intval($rowAuth['accessTokenTimestamp']);
                $tokenType = $rowAuth['tokenType'];
                $getGrantType = $rowAuth['grantType'];
            }

            // USER DATA
            $userData = $this->db->query("SELECT * FROM users_data WHERE BINARY username = ?", array($getFixUsername));
            $resultUserData = $userData->result_array();

            foreach($resultUserData as $rowUserData)
            {
                $statusUser = intval($rowUserData['status']);
                $getNamaLengkap = htmlentities(trim($rowUserData['namaLengkap']), ENT_QUOTES, 'UTF-8');
                $emailFix = htmlentities(trim($rowUserData['email']), ENT_QUOTES, 'UTF-8');
                $privilegenya = $rowUserData['privilege'];
                $userNo = intval($rowUserData['id']);
                //$namaLengkap = htmlentities(trim($rowUserData['namaLengkap']), ENT_QUOTES, 'UTF-8');
                //$getUsername = htmlentities(trim($rowUserData['username']), ENT_QUOTES, 'UTF-8');
                //$getEmail = htmlentities(trim($rowUserData['email']), ENT_QUOTES, 'UTF-8');
            }

            $getRefreshToken = trim($rawData['refreshToken']);

            if(empty(trim($getRefreshToken)))
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>401, 'code'=>'INVALID_REFRESH_TOKEN', 'status'=>false, 'message'=>'Invalid Refresh Token!', 'data'=>[]), 401, 'application/json; charset=UTF-8');

                exit();
            }

            if(!empty(trim($getRefreshToken)))
            {
                $hashGetRefreshToken = hash('sha256', $getRefreshToken . $this->config->item('saltHash1'));

                // validasi refresh token
                $validateRefreshToken = $this->db->query("SELECT username, refreshToken FROM auth_data WHERE BINARY refreshToken = ? AND BINARY username = ?", array($hashGetRefreshToken, $getFixUsername));
                $resultValidateRefreshToken = $validateRefreshToken->result_array();

                if($validateRefreshToken)
                {
                    if(count($resultValidateRefreshToken) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>401, 'code'=>'INVALID_REFRESH_TOKEN', 'status'=>false, 'message'=>'Invalid Refresh Token!', 'data'=>[]), 401, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if(count($resultValidateRefreshToken) > 0)
                    {
                        $setExpiredInSeconds = 604800;
                        $accessTokenTimestamp = time() + $setExpiredInSeconds;
                        $jwtAccessToken = $this->generateJWT($getFixUsername, $getNamaLengkap, $emailFix, $getApiKey, $getGrantType, $privilegenya, $userNo, $accessTokenTimestamp);
                        $jwtAccessTokenHash = hash('sha256', $jwtAccessToken . $this->config->item('saltHash1'));
                        $refreshToken = $this->guidv4();
                        $refreshTokenHash = hash('sha256', $refreshToken . $this->config->item('saltHash1'));
                        $jti = $this->guidv4();
                        $ssoToken = $this->guidv4();
                        $ssoTokenHash = hash('sha256', $ssoToken . $this->config->item('saltHash1'));

                        $lastIPLogin = $_SERVER['REMOTE_ADDR'];
                        $tokenType = "Bearer";

                        // UPDATE AUTH DB
                        $updateAuthData = $this->db->query("UPDATE auth_data SET accessToken = ?, refreshToken = ?, lastIPLogin = ?, accessTokenTimestamp = ?, tokenType = ?, jti = ?, ssoToken = ? WHERE BINARY username = ? AND BINARY refreshToken = ?", array($jwtAccessTokenHash, $refreshTokenHash, $lastIPLogin, $accessTokenTimestamp, $tokenType, $jti, $ssoTokenHash, $getFixUsername, $hashGetRefreshToken));
                                
                        if($updateAuthData)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Token Updated', 'data'=>[
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
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'An error occured when updating the token, please try again later!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }
                    }
                }

                else
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan. Silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }
            }

            exit();
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
        return json_encode($arrayBody);
    }

    public function validateAppVersionData($getAppID, $getAppVersion, $getPlatform)
    {
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
                $isMaintenance = intval($rowAppVersion['isMaintenance']);
            }

            // CHECK JIKA ADA UPDATE
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
                if($isMaintenance == 1)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'MAINTENANCE', 'status'=>false, 'message'=>'Mohon maaf, kami sedang melakukan maintenance. Silakan tunggu proses maintenance selesai ya :)', 'data'=>[
                    ]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                // PERBOLEHKAN AKSES
                // .......
            }
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