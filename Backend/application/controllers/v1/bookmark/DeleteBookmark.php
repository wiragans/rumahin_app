<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class DeleteBookmark extends REST_Controller
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
                $namaLengkap = htmlentities(trim($rowUserData['namaLengkap']), ENT_QUOTES, 'UTF-8');
                $getUsername = htmlentities(trim($rowUserData['username']), ENT_QUOTES, 'UTF-8');
                $getEmail = htmlentities(trim($rowUserData['email']), ENT_QUOTES, 'UTF-8');
                $emailVerified = intval($rowUserData['emailVerified']);
                $nomorWA = $rowUserData['nomorWA'];
                $joinTimestamp = intval($rowUserData['joinTimestamp']);
                $privilege = $rowUserData['privilege'];
                $alamatLengkapnya = htmlentities(trim($rowUserData['alamat']), ENT_QUOTES, 'UTF-8');
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

            $useBookmarkListingID = intval(trim($rawData['useBookmarkListingID']));
            $getListingID = intval(trim($rawData['bookmarkListingID']));
            $katalogUUIDGetInput = trim($rawData['katalogUUID']);

            if($useBookmarkListingID == 1)
            {
                if(empty(trim($getListingID)))
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Listing ID Bookmark Katalog kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }
            }

            if($useBookmarkListingID != 1)
            {
                if(empty(trim($katalogUUIDGetInput)))
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'KatalogUUID Bookmark Katalog kosong!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }
            }

            if($useBookmarkListingID == 1)
            {
                $checkKatalogBookmark = $this->db->query("SELECT * FROM bookmark_lists WHERE BINARY username = ? AND BINARY id = ? AND isDeleted = 0", array($getFixUsername, $getListingID));
                $doCheckKatalogBookmark = $checkKatalogBookmark->result_array();
            }

            if($useBookmarkListingID != 1)
            {
                $checkKatalogBookmark = $this->db->query("SELECT * FROM bookmark_lists WHERE BINARY username = ? AND BINARY katalogUUID = ? AND isDeleted = 0", array($getFixUsername, $katalogUUIDGetInput));
                $doCheckKatalogBookmark = $checkKatalogBookmark->result_array();
            }

            if(count($doCheckKatalogBookmark) <= 0)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Listing ID/UUID Bookmark Katalog tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            if(count($doCheckKatalogBookmark) > 0)
            {
                foreach($doCheckKatalogBookmark as $rowBookmarkData)
                {
                    $katalogUUIDGetFix = $rowBookmarkData['katalogUUID'];
                }
            }
            
            $deletedAtTimestamp = time();
            $deleteKatalogBookmark = $this->db->query("UPDATE bookmark_lists SET isDeleted = 1, deletedAtTimestamp = ? WHERE BINARY username = ? AND BINARY katalogUUID = ? AND isDeleted = 0", array($deletedAtTimestamp, $getFixUsername, $katalogUUIDGetFix));

            if($deleteKatalogBookmark)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Berhasil menghapus bookmark katalog rumah', 'data'=>[]), 200, 'application/json; charset=UTF-8');
            }

            else
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat menghapus bookmark katalog rumah, silakan coba lagi!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
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
}