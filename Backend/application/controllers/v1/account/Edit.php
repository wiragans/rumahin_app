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

class Edit extends REST_Controller
{
	public function __construct($config = 'rest')
	{
		parent::__construct($config);
		$this->load->database();
		$this->load->helper('form', 'url');
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

        if(!in_array('application/x-www-form-urlencoded', $getContentType))
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
            }

            // JIKA ACCESS TOKEN EXPIRED
            $timeNow = time();
            $selisihWaktuAccessTokenTimestamp = $accessTokenTimestamp - $timeNow;

            if($selisihWaktuAccessTokenTimestamp <= 0)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>401, 'code'=>'SESSION_EXPIRED', 'status'=>false, 'message'=>'Session Expired', 'data'=>[]), 401, 'application/json; charset=UTF-8');

                exit();
            }

            // USER DATA
            $userData = $this->db->query("SELECT * FROM users_data WHERE BINARY username = ?", array($getFixUsername));
            $resultUserData = $userData->result_array();

            foreach($resultUserData as $rowUserData)
            {
                $statusUser = intval($rowUserData['status']);
                $currentEmail = $rowUserData['email'];
                $passwordUsernya = $rowUserData['password'];
                $nomorWAUsernya = $rowUserData['nomorWA'];
                $getNamaPengguna = htmlentities(trim($rowUserData['namaLengkap']), ENT_QUOTES, 'UTF-8');
            }

            if($statusUser == 0)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'ACCOUNT_LOCKED', 'status'=>false, 'message'=>'Mohon maaf, akun Anda sedang tidak aktif!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            if($statusUser == 2)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'ACCOUNT_LOCKED', 'status'=>false, 'message'=>'Mohon maaf, akun Anda di-banned karena telah melakukan pelanggaran!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            //

            $namaLengkapGet = htmlentities(trim($this->input->post('namaLengkap')), ENT_QUOTES, 'UTF-8');
            $emailGet = htmlentities(trim($this->input->post('email')), ENT_QUOTES, 'UTF-8');
            $newPasswordGet = trim($this->input->post('newPassword'));
            $nomorWAGet = htmlentities(trim(urlencode($this->input->post('waNumber'))), ENT_QUOTES, 'UTF-8');
            $alamatGet = htmlentities(trim($this->input->post('alamat')), ENT_QUOTES, 'UTF-8');
            $currentPasswordGet = trim($this->input->post('currentPassword'));

            //echo $nomorWAGet;
            //exit();

            $this->load->library('form_validation');

            $rulesForm = array([
                'field'=>'namaLengkap',
                'label'=>'NamaLengkap',
                'rules'=>'required|min_length[4]|max_length[64]|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Bidang nama lengkap harus diisi!')),
                    'min_length'=>json_encode(array('message'=>'Minimal panjang nama lengkap adalah 4 karakter')),
                    'max_length'=>json_encode(array('message'=>'Maksimal panjang nama lengkap adalah 64 karakter'))
                ),
            ],
            [
                'field'=>'email',
                'label'=>'Email',
                'rules'=>'required|valid_email|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Bidang email harus diisi!')),
                    'valid_email'=>json_encode(array('message'=>'Format email harus benar!')),
                ),
            ],
            [
                'field'=>'newPassword',
                'label'=>'NewPassword',
                'rules'=>'min_length[6]|max_length[32]|trim',
                'errors' => array(
                    'min_length'=>json_encode(array('message'=>'Minimal panjang password adalah 6 karakter')),
                    'max_length'=>json_encode(array('message'=>'Maksimal panjang password adalah 32 karakter'))
                ),
            ],
            [
                'field'=>'waNumber',
                'label'=>'WaNumber',
                'rules'=>'required|min_length[6]|max_length[20]|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Nomor WhatsApp (WA) harus diisi!')),
                    'min_length'=>json_encode(array('message'=>'Minimal panjang Nomor WhatsApp (WA) adalah 6 karakter')),
                    'max_length'=>json_encode(array('message'=>'Maksimal panjang Nomor WhatsApp (WA) adalah 20 karakter'))
                ),
            ],
            [
                'field'=>'alamat',
                'label'=>'Alamat',
                'rules'=>'required|min_length[20]|max_length[255]|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Bidang alamat harus diisi dengan detail!')),
                    'min_length'=>json_encode(array('message'=>'Minimal panjang alamat adalah 20 karakter')),
                    'max_length'=>json_encode(array('message'=>'Maksimal panjang alamat adalah 255 karakter'))
                ),
            ],
            [
                'field'=>'currentPassword',
                'label'=>'CurrentPassword',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Mohon isi password Anda saat ini untuk memverifikasi perubahan data'))
                ),
            ]
            );

            $this->form_validation->set_rules($rulesForm);

            if($this->form_validation->run())
            {
                // VALIDASI CURRENT PASSWORD
                $validasiCurrentPasswordHash = hash('sha256', $currentPasswordGet . $this->config->item('saltHash1'));

                if($validasiCurrentPasswordHash != $passwordUsernya)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Maaf, password saat ini yang Anda masukkan tidak valid', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                // VALIDASI FORMAT NOMOR WHATSAPP (WA) contactNumber
                if(!empty(trim($nomorWAGet)))
                {
                    try
                    {
                        $numberWA = PhoneNumber::parse($nomorWAGet);
                        $numberWA->format(PhoneNumberFormat::INTERNATIONAL);

                        $nomorWAGet = $numberWA; // AMBIL DARI FORMAT NOMOR WHATSAPP YANG UDAH DIFIX ATAU DIFORMATED KE WUJUD SEBENARNYA
                    }

                    catch(PhoneNumberParseException $e)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Format nomor WhatsApp (WA) Anda tidak valid. Periksa kembali!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }
                }

                // VALIDASI WA NUMBER
                if(strtolower(trim($nomorWAGet)) != strtolower(trim($nomorWAUsernya)))
                {
                    $cekWANumberExists = $this->db->query("SELECT * FROM users_data WHERE nomorWA = ?", array($nomorWAGet));
                    $resultCekWANumberExists = $cekWANumberExists->result_array();

                    if(count($resultCekWANumberExists) > 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Nomor WhatsApp (WA) telah digunakan oleh pengguna lainnya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    // ... JIKA LOLOS, BERARTI NOMOR WA TERBARU TERSEBUT VALID
                    // MASUKKAN KE TEMPORARY DB GANTI NOMOR WA
                    $currentIPnya = $_SERVER['REMOTE_ADDR'];
                    $issuedAtTimestampWA = time();
                    $changeWATemp = $this->db->query("INSERT INTO ganti_wa_data(username, oldNomorWA, newNomorWA, issuedAtTimestamp, ip) VALUES(?, ?, ?, ?, ?)", array($getFixUsername, $nomorWAUsernya, $nomorWAGet, $issuedAtTimestampWA, $currentIPnya));
                }

                // VALIDASI EMAIL
                $sendTokenKeEmail = false;

                if(strtolower(trim($currentEmail)) != strtolower(trim($emailGet)))
                {
                    $cekEmailExists = $this->db->query("SELECT * FROM users_data WHERE email = ?", array($emailGet));
                    $resultCekEmailExists = $cekEmailExists->result_array();

                    if(count($resultCekEmailExists) > 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Email telah digunakan oleh pengguna lainnya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    // ... JIKA LOLOS, BERARTI EMAIL TERBARU TERSEBUT VALID
                    // MASUKKAN KE TEMPORARY DB GANTI EMAIL

                    // PREVENT DUPLICATION
                    // UPDATE DATA HISTORY SEBELUMNYA: STATUS CAN NOT BE USED KE 1 (TRUE)
                    $preventDuplication = $this->db->query("UPDATE ganti_email_data SET canNotBeUsed = 1 WHERE BINARY username = ? AND canNotBeUsed = 0", array($getFixUsername));

                    // KEMUDIAN TAMBAHKAN
                    $hasUsedTempEmail = 0;
                    $currentIPnya = $_SERVER['REMOTE_ADDR'];
                    $issuedAtTimestampEmail = time();
                    $canNotBeUsedSet = 0;

                    $generateTokenChangeEmail = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64)) . time() . $getFixUsername . "ZptjCuS8KwKDcKkbhWzKsJWueH28fNgz"); // untuk disend ke email terbaru tokennya
                    $generateTokenChangeEmailHash = hash('sha256', $generateTokenChangeEmail . $this->config->item('saltHash1')); // untuk diinput ke db tokennya

                    $changeEmailTemp = $this->db->query("INSERT INTO ganti_email_data(username, oldEmail, newEmail, issuedAtTimestamp, hasUsed, ip, canNotBeUsed, token) VALUES(?, ?, ?, ?, ?, ?, ?, ?)", array($getFixUsername, $currentEmail, $emailGet, $issuedAtTimestampEmail, $hasUsedTempEmail, $currentIPnya, $canNotBeUsedSet, $generateTokenChangeEmailHash));

                    $sendTokenKeEmail = true;
                    $linkChangeEmailnya = $this->config->item('baseUrlChangeEmail1') . $generateTokenChangeEmail;

                    // SETELAHNYA, USER HARUS CEK EMAIL TERBARUNYA UNTUK MENGONFIRMASI EMAIL TERSEBUT ADALAH MILIK DIA
                }

                //
                if(empty(trim($newPasswordGet)))
                {
                    $updateAkunData = $this->db->query("UPDATE users_data SET namaLengkap = ?, nomorWA = ?, alamat = ? WHERE BINARY username = ?", array($namaLengkapGet, $nomorWAGet, $alamatGet, $getFixUsername));
                }
                
                if(!empty(trim($newPasswordGet)))
                {
                    $newPasswordGetHash = hash('sha256', $newPasswordGet . $this->config->item('saltHash1'));
                    $updateAkunData = $this->db->query("UPDATE users_data SET namaLengkap = ?, password = ?, nomorWA = ?, alamat = ? WHERE BINARY username = ?", array($namaLengkapGet, $newPasswordGetHash, $nomorWAGet, $alamatGet, $getFixUsername));
                }

                if($updateAkunData)
                {
                    if($sendTokenKeEmail == true)
                    {
                        $this->sendTokenChangeEmail($linkChangeEmailnya, $currentEmail, $emailGet, $getFixUsername, $getNamaPengguna);

                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Update data akun sukses. Kami mendeteksi bahwa Anda ingin melakukan perubahan email, silakan lakukan konfirmasi pada link yang kami berikan ke email terbaru Anda: ' . $emailGet, 'data'=>[]), 200, 'application/json; charset=UTF-8');
                    }
                    
                    if($sendTokenKeEmail ==  false)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Update data akun sukses', 'data'=>[]), 200, 'application/json; charset=UTF-8');
                    }

                    exit();
                }

                else
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat memperbarui data akun Anda, silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                exit();
            }

            else
            {
                $arrayError = array();

                for($ulang = 0; $ulang < sizeof($rulesForm); $ulang++)
                {
                    $errorMessage = form_error($rulesForm[$ulang]['field'], '', '');

                    $isError = true;

                    if(empty(trim($errorMessage)))
                    {
                        $isError = false;
                    }

                    $arrayError2 = [
                        'isError'=>$isError,
                        'errorsField'=>$rulesForm[$ulang]['field'],
                        'errorsFieldHighlight'=>$rulesForm[$ulang]['field'] . 'Error',
                        'errorsMessage'=>json_decode(strip_tags(form_error($rulesForm[$ulang]['field'], '', '')), true)
                    ];

                    array_push($arrayError, $arrayError2);
                }

                //echo form_error('developerName', '<div class="error">', '</div>');

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'EDIT_ACCOUNT_ERROR', 'status'=>false, 'message'=>'Permintaan tidak diterima, periksa kembali data yang diinputkan di atas!', 
                    'data'=>[
                    'errors'=>$arrayError
                    ]), 200, 'application/json; charset=UTF-8');

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

    public function sendTokenChangeEmail($tokenLink, $oldEmailnya, $newEmailnya, $getUsernamenya, $getNamanya)
	{
        $tokenLinkChangeEmail = $tokenLink;
        $oldEmailUsernya = $oldEmailnya;
		$getEmailUserNew = htmlentities($newEmailnya, ENT_QUOTES, 'UTF-8');
		$getUsernameUser = htmlentities($getUsernamenya, ENT_QUOTES, 'UTF-8');
        $getNama = htmlentities($getNamanya, ENT_QUOTES, 'UTF-8');

		$mail = new PHPMailer();
		$mail->IsSMTP();

		$mail->SMTPDebug  = 0;
		$mail->SMTPAuth   = TRUE;
		$mail->SMTPSecure = $this->config->item('MethodSmtpNoReply1');
		$mail->Port       = $this->config->item('PortSmtpNoReply1');
		$mail->Host       = $this->config->item('HostSmtpNoReply1');
		$mail->Username   = $this->config->item('UsernameSmtpNoReply1');
		$mail->Password   = $this->config->item('PasswordSmtpNoReply1');
		$mail->CharSet = $this->config->item('CharsetSmtpNoReply1');

		$mail->IsHTML(true);
		$mail->AddAddress($getEmailUserNew, $getUsernameUser);
		$mail->SetFrom($this->config->item('UsernameSmtpNoReply1'), "RumahinApp");
		$mail->Subject = "Konfirmasi Ganti Email Akun RumahinApp";
		$content = 'Halo, <b>' . $getNama . '</b>.<br><br>Kami mengirimkan Anda pesan karena Anda melakukan perubahan email dari <b>' . $oldEmailUsernya . '</b> ke <b>' . $getEmailUserNew . '</b>. Jika Anda setuju ingin melanjutkan, silakan klik link ini untuk mengonfirmasi bahwa permintaan ini adalah benar-benar dari Anda. Link konfirmasi: ' . $tokenLinkChangeEmail . '<br><br>( Pesan ini adalah pesan otomatis yang di-generate oleh sistem. Jangan membalas pesan ini! )';

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