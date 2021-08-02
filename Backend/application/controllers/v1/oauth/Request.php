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

class Request extends REST_Controller
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
                
                // VALIDASI GRANT TYPE
                if($getGrantType == "otp")
                {
                    // CHECK AKUN
                    $cekAkun = $this->db->query("SELECT * FROM users_data WHERE (BINARY username = ? or email = ?) AND BINARY privilege = ?", array($getAccount, $getAccount, $getScopes));
                    $doCekAkun = $cekAkun->result_array();

                    if(count($doCekAkun) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Akun tidak ditemukan. Mohon periksa kembali username atau email yang diinputkan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if(count($doCekAkun) > 0)
                    {
                        foreach($doCekAkun as $rowAkun)
                        {
                            $getFixNamaLengkap = htmlentities(trim($rowAkun['namaLengkap']), ENT_QUOTES, 'UTF-8');
                            $getFixUsername = htmlentities(trim($rowAkun['username']), ENT_QUOTES, 'UTF-8');
                            $getFixEmail = htmlentities(trim($rowAkun['email']), ENT_QUOTES, 'UTF-8');
                            $emailVerified = intval($rowAkun['emailVerified']);

                            $statusAkun = intval($rowAkun['status']);
                        }

                        if($emailVerified != 1)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'EMAIL_NOT_VERIFIED', 'status'=>false, 'message'=>'Email akun belum diverifikasi, yuk perika email kamu sebelumnya untuk mengaktifkan akun ini :)', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        if($statusAkun == 0)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Akun ini sudah tidak aktif lagi. Silakan hubungi Admin di nomor WhatsApp +6287823867941 untuk informasi lebih lanjut atau pengajuan naik banding!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        if($statusAkun == 2)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Akun dikunci oleh Admin karena terindikasi pelanggaran. Silakan hubungi Admin di nomor WhatsApp +6287823867941 untuk informasi lebih lanjut atau pengajuan naik banding!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        if(strtolower($getAccount) != strtolower($getFixEmail))
                        {
                            $getFixEmailSensor = $this->sensorEmail($getFixEmail);
                        }

                        if(strtolower($getAccount) == strtolower($getFixEmail))
                        {
                            $getFixEmailSensor = $getFixEmail;
                        }

                        $timeNow = time();
                        $challengeTokenExpiresAt = $timeNow + 300; // 5 MENIT
                        $challengeTokenGenerateRandom = bin2hex(openssl_random_pseudo_bytes(64));
                        $challengeToken = hash('sha256', $challengeTokenGenerateRandom . $getGrantType . $getFixUsername . $getFixEmail . $timeNow . $this->config->item('saltKeyRandomString'));
                        $challengeTokenHash = hash('sha256', $challengeToken . $this->config->item('saltKeyChallengeToken'));

                        // INSERT INTO DB
                        $grantTypeSet = $getGrantType;
                        $setOTPCode = $this->generateOTPCode();
                        //echo $setOTPCode; exit();
                        $setOTPCodeHash = hash('sha256', $setOTPCode . $this->config->item('saltKeyOTP'));
                        $insertChallengeToken = $this->db->query("INSERT INTO auth_data(username, challengeToken, accessToken, refreshToken, lastIPLogin, accessTokenTimestamp, tokenType, grantType, tempOTP, challengeTokenExpiresAt) VALUES(?, ?, '', '', '', '', '', ?, ?, ?)", array($getFixUsername, $challengeTokenHash, $grantTypeSet, $setOTPCodeHash, $challengeTokenExpiresAt));

                        if($insertChallengeToken)
                        {
                            $this->doSendOTPCode($setOTPCode, $getFixEmail, $getFixUsername, $getFixNamaLengkap);

                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Masukkan Kode OTP yang dikirimkan ke email ' . $getFixEmailSensor, 'data'=>[
                                'challengeToken'=>$challengeToken,
                                'grantType'=>$grantTypeSet,
                                'canResend'=>true,
                                'canResendInSeconds'=>60,
                                'input'=>[
                                    'length'=>6,
                                    'type'=>'number',
                                    'label'=>'Kode OTP',
                                    'placeholder'=>'Kode OTP'
                                ]
                            ]), 200, 'application/json; charset=UTF-8');
                        }

                        else
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
                        }
                        
                        exit();
                    }

                    exit();
                }

                else if($getGrantType == "password")
                {
                    // CHECK AKUN
                    $cekAkun = $this->db->query("SELECT * FROM users_data WHERE (BINARY username = ? or email = ?) AND BINARY privilege = ?", array($getAccount, $getAccount, $getScopes));
                    $doCekAkun = $cekAkun->result_array();

                    if(count($doCekAkun) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Akun tidak ditemukan. Mohon periksa kembali username atau email yang diinputkan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if(count($doCekAkun) > 0)
                    {
                        foreach($doCekAkun as $rowAkun)
                        {
                            $getFixNamaLengkap = htmlentities(trim($rowAkun['namaLengkap']), ENT_QUOTES, 'UTF-8');
                            $getFixUsername = htmlentities(trim($rowAkun['username']), ENT_QUOTES, 'UTF-8');
                            $getFixEmail = htmlentities(trim($rowAkun['email']), ENT_QUOTES, 'UTF-8');
                            $emailVerified = intval($rowAkun['emailVerified']);

                            $statusAkun = intval($rowAkun['status']);
                        }

                        if($emailVerified != 1)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'EMAIL_NOT_VERIFIED', 'status'=>false, 'message'=>'Email akun belum diverifikasi, yuk perika email kamu sebelumnya untuk mengaktifkan akun ini :)', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        if($statusAkun == 0)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Akun ini sudah tidak aktif lagi. Silakan hubungi Admin di nomor WhatsApp +6287823867941 untuk informasi lebih lanjut atau pengajuan naik banding!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        if($statusAkun == 2)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Akun dikunci oleh Admin karena terindikasi pelanggaran. Silakan hubungi Admin di nomor WhatsApp +6287823867941 untuk informasi lebih lanjut atau pengajuan naik banding!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        $timeNow = time();
                        $challengeTokenExpiresAt = $timeNow + 300; // 5 MENIT
                        $challengeTokenGenerateRandom = bin2hex(openssl_random_pseudo_bytes(64));
                        $challengeToken = hash('sha256', $challengeTokenGenerateRandom . $getGrantType . $getFixUsername . $getFixEmail . $timeNow . $this->config->item('saltKeyRandomString'));
                        $challengeTokenHash = hash('sha256', $challengeToken . $this->config->item('saltKeyChallengeToken'));

                        // INSERT INTO DB
                        $grantTypeSet = $getGrantType;
                        $insertChallengeToken = $this->db->query("INSERT INTO auth_data(username, challengeToken, accessToken, refreshToken, lastIPLogin, accessTokenTimestamp, tokenType, grantType, challengeTokenExpiresAt) VALUES(?, ?, '', '', '', '', '', ?, ?)", array($getFixUsername, $challengeTokenHash, $grantTypeSet, $challengeTokenExpiresAt));

                        if($insertChallengeToken)
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Masukkan Password Anda', 'data'=>[
                                'challengeToken'=>$challengeToken,
                                'grantType'=>$grantTypeSet,
                                'canResend'=>false,
                                'canResendInSeconds'=>null,
                                'input'=>[
                                    'length'=>32,
                                    'type'=>'text',
                                    'label'=>'Password',
                                    'placeholder'=>'Password'
                                ]
                            ]), 200, 'application/json; charset=UTF-8');
                        }

                        else
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
                        }
                        
                        exit();
                    }

                    exit();
                }

                else
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'INVALID_GRANT_TYPE', 'status'=>false, 'message'=>'Invalid Grant Type', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }
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
}