<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require APPPATH . '/libraries/SMTP/Exception.php';
require APPPATH . '/libraries/SMTP/PHPMailer.php';
require APPPATH . '/libraries/SMTP/SMTP.php';
date_default_timezone_set('Asia/Jakarta');

class RequestResetPassword extends REST_Controller
{
	public function __construct($config = 'rest')
	{
		parent::__construct($config);
		$this->load->database();
		$this->load->helper('form', 'url');
		$this->load->helper('email');
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
        $getEmail = htmlentities(trim($rawData['email']), ENT_QUOTES, 'UTF-8');

        // CHECK JIKA EMAIL EXISTS AND VERIFIED
        $checkEmail = $this->db->select('username, email, namaLengkap')->from('users_data')->where(['email'=>$getEmail, 'emailVerified'=>1])->get();
        $resultCheckEmail = $checkEmail->result_array();

        //echo "OK";
        //var_dump($resultCheckEmail); exit();

        if(count($resultCheckEmail) <= 0)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Jika email Anda terdaftar dan terverifikasi, maka Anda akan menerima link pemulihan password di email tersebut. Silakan dapat Anda periksa!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
        }

        if(count($resultCheckEmail) > 0)
        {
            foreach($resultCheckEmail as $rowEmailAkun)
            {
                $getFixUsername = $rowEmailAkun['username'];
                $getNamaLengkap = htmlentities(trim($rowEmailAkun['namaLengkap']), ENT_QUOTES, 'UTF-8');
            }

            // KIRIM LINK RESET PASSWORD KE EMAIL
            $randomTokenReset = bin2hex(openssl_random_pseudo_bytes(64));
            $tokenReset = hash('sha256', $getFixUsername . $getEmail . time() . $randomTokenReset);
            $tokenResetHash = hash('sha256', $tokenReset . $this->config->item('saltHash1'));
            $linknya = $this->config->item('baseUrlResetPasswordConfirm1') . $tokenReset . "&email=" . $getEmail;

            $hasUsed = 0;
            $requestTimestamp = time();
            $expiredTimestamp = $requestTimestamp + 3600;
            $ipnya = $_SERVER['REMOTE_ADDR'];
            $isWhenLogin = 1;

            $addResetData = $this->db->query("INSERT INTO reset_password_data(username, tokenReset, hasUsed, requestTimestamp, expiredTimestamp, ip, emailnya, isWhenLogin) VALUES(?, ?, ?, ?, ?, ?, ?, ?)", array($getFixUsername, $tokenResetHash, $hasUsed, $requestTimestamp, $expiredTimestamp, $ipnya, $getEmail, $isWhenLogin));

            if($addResetData)
            {
                $this->doSendLinkResetPassword($linknya, $getEmail, $getFixUsername, $getNamaLengkap);

                //

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Jika email Anda terdaftar dan terverifikasi, maka Anda akan menerima link pemulihan password di email tersebut. Silakan dapat Anda periksa!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            else
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan, silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

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
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8', 'WWW-Authenticate: Basic realm="RumahinRealms"'), array('statusCode'=>401, 'code'=>'UNAUTHORIZED', 'status'=>false, 'message'=>'Unauthorized', 'data'=>[]), 401, 'application/json; charset=UTF-8');

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

    public function doSendLinkResetPassword($linknya, $emailUsernya, $getUsernamenya, $getNamanya)
	{
		$link = $linknya;
		$getEmailUser = htmlentities($emailUsernya, ENT_QUOTES, 'UTF-8');
		$getUsernameUser = htmlentities($getUsernamenya, ENT_QUOTES, 'UTF-8');
		$getNama = htmlentities($getNamanya, ENT_QUOTES, 'UTF-8');

		$mail = new PHPMailer();
		$mail->IsSMTP();

		//var_dump($getEmailUser);

		$mail->SMTPDebug  = 0;
		$mail->SMTPAuth   = TRUE;
		$mail->SMTPSecure = "tls";
		$mail->Port       = 587;
		$mail->Host       = "giga.cangkirhost.net";
		$mail->Username   = "noreply@kmsp-store.com";
		$mail->Password   = "TtCuqCr3P";
        $mail->CharSet = 'UTF-8';
        
        $timeExpired = time() + 3600;
        $timeExpiredReal = date("Y-m-d H:i:s", $timeExpired) . " WIB";

		$mail->IsHTML(true);
		$mail->AddAddress($getEmailUser, $getUsernameUser);
		$mail->SetFrom("noreply@kmsp-store.com", "RumahinApp");
		$mail->Subject = "Link Reset Password Akun RumahinApp";
		$content = 'Halo, <b>' . $getNama . '</b>.<br><br>Berikut ini merupakan link untuk melakukan reset password akun RumahinApp Anda: ' . $link . '<br><br>Demi keamanan, jangan berikan link ini kepada siapapun termasuk yang mengatasnamakan kami! Waspada Penipuan! Berlaku hingga ' . $timeExpiredReal . ' atau sekali pakai saja.<br><br>( Pesan ini adalah pesan otomatis yang di-generate oleh sistem. Jangan membalas pesan ini! )';

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
}