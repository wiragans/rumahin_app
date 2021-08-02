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

class Register extends REST_Controller
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
        $getNamaLengkap = htmlentities(trim($rawData['namaLengkap']), ENT_QUOTES, 'UTF-8');
        $getUsername = htmlentities(trim($rawData['username']), ENT_QUOTES, 'UTF-8');
        $getEmail = htmlentities(trim($rawData['email']), ENT_QUOTES, 'UTF-8');
        $getPassword = htmlentities(trim($rawData['password']), ENT_QUOTES, 'UTF-8');
        $repeatPassword = htmlentities(trim($rawData['repeatPassword']), ENT_QUOTES, 'UTF-8');
        $getNomorWA = htmlentities(trim($rawData['nomorWA']), ENT_QUOTES, 'UTF-8');
        $getAlamat = htmlentities(trim($rawData['alamat']), ENT_QUOTES, 'UTF-8');

        if(empty(trim($getNamaLengkap)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Nama Lengkap Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(empty(trim($getUsername)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Username Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(empty(trim($getEmail)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Email Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(!valid_email($getEmail))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Format email tidak valid. Periksa kembali!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(empty(trim($getPassword)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Password Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(empty(trim($repeatPassword)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Repeat Password Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(empty(trim($getNomorWA)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Nomor WhatsApp Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        // JIKA TIDAK KOSONG
        if(strlen($getNamaLengkap) < 4 || strlen($getNamaLengkap) > 64)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Panjang nama lengkap minimal 4 dan maksimal 64 karakter!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(empty(trim($getAlamat)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Alamat Masih Kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(strlen($getUsername) < 6 || strlen($getUsername) > 32)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Panjang Username minimal 6 dan maksimal 32 karakter!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(strtolower(trim($getUsername)) == strtolower(trim($getNomorWA)) || (preg_match('/^[0-9]+$/', str_replace("+", "", strtolower(trim($getUsername))))))
		{
			echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Username tidak boleh diisi dengan format nomor telepon!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

			exit();
        }
        
        if(strtolower(trim($getUsername)) == strtolower(trim($getEmail)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Tidak boleh menggunakan email di rumpang isian username!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(strlen($getPassword) < 6 || strlen($getPassword) > 32)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Panjang Password minimal 6 dan maksimal 32 karakter!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if($getPassword != $repeatPassword)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Repeat Password tidak sama dengan Password!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        // VALIDASI FORMAT NOMOR WHATSAPP (WA)
        if(!empty(trim($getNomorWA)))
		{
			try
			{
				$numberWA = PhoneNumber::parse($getNomorWA);
				$numberWA->format(PhoneNumberFormat::INTERNATIONAL);

				$getNomorWA = $numberWA; // AMBIL DARI FORMAT NOMOR WHATSAPP YANG UDAH DIFIX ATAU DIFORMATED KE WUJUD SEBENARNYA
			}

			catch(PhoneNumberParseException $e)
			{
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Format nomor WhatsApp (WA) tidak valid. Periksa kembali!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
			}
        }
        
        // CHECK USERNAME ATAU EMAIL SUDAH ADA
        $cekUserExists = $this->db->query("SELECT username, email FROM users_data WHERE username = ? OR email = ?", array($getUsername, $getEmail));
        $resultCekUserExists = $cekUserExists->result_array();

        if(count($resultCekUserExists) > 0)
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Username atau email sudah digunakan oleh pengguna lain, silakan gunakan username atau email yang lainnya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if(count($resultCekUserExists) <= 0)
        {
            $cekWAExists = $this->db->query("SELECT nomorWA FROM users_data WHERE BINARY nomorWA = ?", array($getNomorWA));
            $resultCekWAExists = $cekWAExists->result_array();

            if(count($resultCekWAExists) > 0)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Nomor WhatsApp (WA) sudah digunakan oleh pengguna lainnya. Silakan gunakan nomor WA Anda yang lainnya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            // INSERT DATA KE DATABASE
            $joinTimestamp = time();
            $tokenVerifikasiEmailExpired = $joinTimestamp + 604800;
            $emailVerified = 0;
            $statusUser = 1;
            $privilegeUser = "user";

            $passwordHash = hash('sha256', $getPassword . $this->config->item('saltHash1'));
            $registrationRandomString = bin2hex(openssl_random_pseudo_bytes(64));
            $tokenLinkVerifyEmail = hash('sha256', $getUsername . $getEmail . $joinTimestamp . $registrationRandomString . $this->config->item('saltKeyRandomString'));
            //echo $this->config->item('baseUrlVerifyEmail1');
            //exit();

            $linkVerifyEmail = $this->config->item('baseUrlVerifyEmail1') . $tokenLinkVerifyEmail;

            $insertData = $this->db->query("INSERT INTO users_data(namaLengkap, username, email, password, joinTimestamp, emailVerified, status, privilege, nomorWA, alamat, tokenVerifikasiEmail, tokenVerifikasiEmailExpired) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($getNamaLengkap, $getUsername, $getEmail, $passwordHash, $joinTimestamp, $emailVerified, $statusUser, $privilegeUser, $getNomorWA, $getAlamat, $tokenLinkVerifyEmail, $tokenVerifikasiEmailExpired));

            if($insertData)
            {
                $this->doSendLinkRegistrasi($linkVerifyEmail, $getEmail, $getUsername, $getNamaLengkap);

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Registrasi sukses, silakan periksa email ' . $getEmail . ' untuk mengaktifkan akun Anda', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            else
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat melakukan registrasi, silakan coba beberapa saat lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

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
        return json_encode($arrayBody);
    }

    public function doSendLinkRegistrasi($linknya, $emailUsernya, $getUsernamenya, $getNamanya)
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

		//$headersSMTP = "Content-Type: text/html; charset=UTF-8";

		$mail->IsHTML(true);
		$mail->AddAddress($getEmailUser, $getUsernameUser);
		$mail->SetFrom("noreply@kmsp-store.com", "RumahinApp");
		$mail->Subject = "Verifikasi Akun Rumahin";
		$content = 'Halo, <b>' . $getNama . '</b>.<br><br>Terima kasih telah melakukan pendaftaran akun di Rumahin. Silakan klik link / tautan di bawah ini untuk melakukan verifikasi akun Anda. Atau jika tidak bisa diklik, silakan copy-paste link/tautan berikut ke address bar browser Anda dan kemudian enter/go. <b>Abaikan pesan ini apabila ini bukan Anda</b>. <br><br>Link Verifikasi Akun: <b>' . $link .'</b>.<br><br>Link / tautan ini akan expired dalam <b>7 hari kedepan</b>, <b>segera verifikasi apabila tidak ingin akun Anda terhapus dari sistem kami</b>.<br><br>( Pesan ini adalah pesan otomatis yang di-generate oleh sistem. Jangan membalas pesan ini! )';

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