<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class Query extends REST_Controller
{
	public function __construct($config = 'rest')
	{
		parent::__construct($config);
		$this->load->database();
		$this->load->helper('form', 'url');
	}
    
	public function index_get()
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
            $getQuerySearch = htmlentities(trim($this->input->get('search_query')), ENT_QUOTES, 'UTF-8');
            $getPageOffset = intval($this->input->get('page'));
            $getLimitOffset = intval($this->input->get('limit'));

            if($getPageOffset <= 0)
            {
                $getPageOffset = 1;
            }

            if($getLimitOffset <= 0)
            {
                $getLimitOffset = 10;
            }

            if($getLimitOffset > 30)
            {
                $getLimitOffset = 30;
            }

            $startFrom = intval(($getPageOffset - 1) * $getLimitOffset);

            //echo $startFrom;
            //exit();
            
            // SELECT DISTINCT token_data.dokumen_id, token_data.token, token_data.tokenstem, dokumen.nama_file, dokumen.file_format, dokumen.uploaded_by, dokumen.file_url, dokumen.uploaded_at_timestamp, posts.post_title, posts.post_desc, posts.author, posts.creator, posts.CreationDate, posts.ModDate, posts.Producer, posts.Pages FROM token_data INNER JOIN dokumen ON BINARY dokumen.dokumen_id = token_data.dokumen_id INNER JOIN posts ON BINARY posts.dokumen_id = token_data.dokumen_id WHERE MATCH (token_data.token, token_data.tokenstem) AGAINST (? IN BOOLEAN MODE) ORDER BY dokumen.uploaded_at_timestamp DESC
            //$searchQuery = "SELECT DISTINCT katalog_data.id, katalog_data.katalogUUID, katalog_data.katalogName, katalog_data.katalogDesc, katalog_data.tayangTimestamp, katalog_data.luasTanah, katalog_data.luasBangunan, katalog_data.jumlahKamarMandi, katalog_data.jumlahKamarTidur, katalog_data.jumlahRuangTamu, katalog_data.jumlahGarasi, katalog_data.jumlahRuangKeluarga, katalog_data.jumlahRuangMakan, katalog_data.jumlahGudang, katalog_data.jumlahSerambi, katalog_data.jumlahTingkat, katalog_data.alamat, katalog_data.harga, katalog_data.developerName, katalog_data.contactNumber, katalog_data.emailDeveloper, katalog_data.tahunDibuat, katalog_data.isSecond, katalog_data.isDisewakan, katalog_data.modeSewa, katalog_data.useAR, misc_katalog_spec.tipePropertiRumah, misc_katalog_spec.conditionMeasurement, misc_katalog_spec.perlengkapanPerabotan, misc_katalog_spec.dayaListrik, katalog_video_data.videoUrl, stem_token_data.token, stem_token_data.tokenstem, ar_data.objectFileUUID, ar_data.objectFileURL, ar_data.objectFileDiffuseTextureURL, ar_data.markerBase64Format, ar_data.markerUrl FROM stem_token_data INNER JOIN katalog_data ON BINARY katalog_data.katalogUUID = stem_token_data.katalogUUID INNER JOIN katalog_images_data ON BINARY katalog_images_data.katalogUUID = katalog_data.katalogUUID INNER JOIN katalog_video_data ON BINARY katalog_video_data.katalogUUID = katalog_data.katalogUUID INNER JOIN misc_katalog_spec ON BINARY misc_katalog_spec.katalogUUID = katalog_data.katalogUUID INNER JOIN ar_data ON BINARY ar_data.katalogUUID = katalog_data.katalogUUID  WHERE MATCH (stem_token_data.token, stem_token_data.tokenstem) AGAINST (? IN BOOLEAN MODE) AND katalog_data.status = 1 ORDER BY katalog_data.tayangTimestamp DESC LIMIT ?, ?"; // OLD ONE

            $searchQuery = "SELECT DISTINCT katalog_data.id, katalog_data.katalogUUID, katalog_data.katalogName, katalog_data.katalogDesc, katalog_data.tayangTimestamp, katalog_data.luasTanah, katalog_data.luasBangunan, katalog_data.jumlahKamarMandi, katalog_data.jumlahKamarTidur, katalog_data.jumlahRuangTamu, katalog_data.jumlahGarasi, katalog_data.jumlahRuangKeluarga, katalog_data.jumlahRuangMakan, katalog_data.jumlahGudang, katalog_data.jumlahSerambi, katalog_data.jumlahTingkat, katalog_data.alamat, katalog_data.harga, katalog_data.developerName, katalog_data.contactNumber, katalog_data.emailDeveloper, katalog_data.tahunDibuat, katalog_data.isSecond, katalog_data.isDisewakan, katalog_data.modeSewa, katalog_data.useAR, misc_katalog_spec.tipePropertiRumah, misc_katalog_spec.conditionMeasurement, misc_katalog_spec.perlengkapanPerabotan, misc_katalog_spec.dayaListrik, stem_token_data.token, stem_token_data.tokenstem FROM stem_token_data INNER JOIN katalog_data ON BINARY katalog_data.katalogUUID = stem_token_data.katalogUUID INNER JOIN katalog_images_data ON BINARY katalog_images_data.katalogUUID = katalog_data.katalogUUID INNER JOIN misc_katalog_spec ON BINARY misc_katalog_spec.katalogUUID = katalog_data.katalogUUID WHERE MATCH (stem_token_data.token, stem_token_data.tokenstem) AGAINST (? IN BOOLEAN MODE) AND katalog_data.status = 1 ORDER BY katalog_data.tayangTimestamp DESC LIMIT ?, ?";

            $doSearchQuery = $this->db->query($searchQuery, array($getQuerySearch, intval($startFrom), intval($getLimitOffset)));

            //$searchQuery = "SELECT DISTINCT katalog_data.id, katalog_data.katalogUUID, katalog_data.katalogName, katalog_data.katalogDesc, katalog_data.tayangTimestamp, katalog_data.luasTanah, katalog_data.luasBangunan, katalog_data.jumlahKamarMandi, katalog_data.jumlahKamarTidur, katalog_data.jumlahRuangTamu, katalog_data.jumlahGarasi, katalog_data.jumlahRuangKeluarga, katalog_data.jumlahRuangMakan, katalog_data.jumlahGudang, katalog_data.jumlahSerambi, katalog_data.jumlahTingkat, katalog_data.alamat, katalog_data.harga, katalog_data.developerName, katalog_data.contactNumber, katalog_data.emailDeveloper, katalog_data.tahunDibuat, katalog_data.isSecond, katalog_data.isDisewakan, katalog_data.modeSewa, katalog_data.useAR, misc_katalog_spec.tipePropertiRumah, misc_katalog_spec.conditionMeasurement, misc_katalog_spec.perlengkapanPerabotan, misc_katalog_spec.dayaListrik, katalog_video_data.videoUrl, stem_token_data.token, stem_token_data.tokenstem, ar_data.objectFileUUID, ar_data.objectFileURL, ar_data.objectFileDiffuseTextureURL, ar_data.markerBase64Format, ar_data.markerUrl FROM stem_token_data INNER JOIN katalog_data ON BINARY katalog_data.katalogUUID = stem_token_data.katalogUUID INNER JOIN katalog_images_data ON BINARY katalog_images_data.katalogUUID = katalog_data.katalogUUID INNER JOIN katalog_video_data ON BINARY katalog_video_data.katalogUUID = katalog_data.katalogUUID INNER JOIN misc_katalog_spec ON BINARY misc_katalog_spec.katalogUUID = katalog_data.katalogUUID INNER JOIN ar_data ON BINARY ar_data.katalogUUID = katalog_data.katalogUUID  WHERE MATCH (stem_token_data.token, stem_token_data.tokenstem) AGAINST (? IN BOOLEAN MODE) ORDER BY katalog_data.tayangTimestamp";
            //$doSearchQuery = $this->db->query($searchQuery, array($getQuerySearch));
            
            if($doSearchQuery)
			{
                $arrayResult = array();
                $katalogUUIDTempArray = array();

                $resultSearchQuery = $doSearchQuery->result_array();

                foreach($resultSearchQuery as $rowResultQuery)
                {
                    if($rowResultQuery['useAR'] == 1)
                    {
                        $arData = [
                            'objectFileUUID'=>$rowResultQuery['objectFileUUID'],
                            'objectFileURL'=>$rowResultQuery['objectFileURL'],
                            'objectFileDiffuseTextureURL'=>$rowResultQuery['objectFileDiffuseTextureURL'],
                            'markerBase64Format'=>$rowResultQuery['markerBase64Format'],
                            'markerUrl'=>$rowResultQuery['markerUrl']
                        ];
                    }

                    else
                    {
                        $arData = [
                            
                        ];
                    }

                    //

                    $developerEmailnya = $rowResultQuery['emailDeveloper'];

                    if(empty(trim($rowResultQuery['emailDeveloper'])))
                    {
                        $developerEmailnya = "-";
                    }

                    $getTipePropertiRumahInString = $this->db->query("SELECT * FROM rumah_data WHERE BINARY kodeTipeRumah = ?", array($rowResultQuery['tipePropertiRumah']));
                    $getTipePropertiRumahInStringResult = $getTipePropertiRumahInString->result_array();

                    foreach($getTipePropertiRumahInStringResult as $rowStringPropertiRumah)
                    {
                        $tipePropertiRumahInStringReal = $rowStringPropertiRumah['tipePropertiRumahInString'];
                    }

                    $getPriceRumah = intval($rowResultQuery['harga']);
                    $getPriceRumahString = "Rp. " . number_format($getPriceRumah,2,',','.');

                    //
                    $katalogImagesData = array();

                    $getImageData = $this->db->query("SELECT * FROM katalog_images_data WHERE katalogUUID = ?", array($rowResultQuery['katalogUUID']));
                    $getImageDataResult = $getImageData->result_array();

                    foreach($getImageDataResult as $rowImageResult)
                    {
                        $katalogImagesData2 = [
                            'imagesUrl'=>$this->config->item('baseUrlKatalogImages') . $rowImageResult['imagesUrl']
                        ];

                        array_push($katalogImagesData, $katalogImagesData2);
                    }

                    //

                    $arrayResult2 = [
                        'katalogID'=>intval($rowResultQuery['id']),
                        'katalogUUID'=>$rowResultQuery['katalogUUID'],
                        'katalogName'=>$rowResultQuery['katalogName'],
                        'katalogDesc'=>$rowResultQuery['katalogDesc'],
                        'details'=>[
                            'luasTanah'=>$rowResultQuery['luasTanah'],
                            'luasBangunan'=>$rowResultQuery['luasBangunan'],
                            'jumlahKamarMandi'=>intval($rowResultQuery['jumlahKamarMandi']),
                            'jumlahKamarTidur'=>intval($rowResultQuery['jumlahKamarTidur']),
                            'jumlahRuangTamu'=>intval($rowResultQuery['jumlahRuangTamu']),
                            'jumlahGarasi'=>intval($rowResultQuery['jumlahGarasi']),
                            'jumlahRuangKeluarga'=>intval($rowResultQuery['jumlahRuangKeluarga']),
                            'jumlahRuangMakan'=>intval($rowResultQuery['jumlahRuangMakan']),
                            'jumlahGudang'=>intval($rowResultQuery['jumlahGudang']),
                            'jumlahSerambi'=>intval($rowResultQuery['jumlahSerambi']),
                            'jumlahTingkat'=>intval($rowResultQuery['jumlahTingkat']),
                            'alamat'=>$rowResultQuery['alamat'],
                            'tahunDibuat'=>$rowResultQuery['tahunDibuat']
                        ],
                        'miscDetails'=>[
                            'tipePropertiRumah'=>$rowResultQuery['tipePropertiRumah'],
                            'tipePropertiRumahInString'=>$tipePropertiRumahInStringReal,
                            'conditionMeasurement'=>$rowResultQuery['conditionMeasurement'],
                            'perlengkapanPerabotan'=>$rowResultQuery['perlengkapanPerabotan'],
                            'dayaListrik'=>$rowResultQuery['dayaListrik']
                        ],
                        'developerInfo'=>[
                            'developerName'=>$rowResultQuery['developerName'],
                            'developerEmail'=>$developerEmailnya,
                            'developerWhatsApp'=>[
                                'number'=>$rowResultQuery['contactNumber'],
                                'clickTrackingParams'=>[
                                    'simpleText'=>'Hubungi melalui WhatsApp',
                                    'href'=>'https://wa.me/' . $rowResultQuery['contactNumber']
                                ]
                            ]
                        ],
                        'isSecond'=>intval($rowResultQuery['isSecond']),
                        'isDisewakan'=>intval($rowResultQuery['isDisewakan']),
                        'modeSewa'=>$rowResultQuery['modeSewa'],
                        'price'=>[
                            'priceInt'=>$getPriceRumah,
                            'priceStr'=>$getPriceRumahString
                        ],
                        'katalogImagesData'=>$katalogImagesData,
                        'katalogVideoData'=>[
                            'videoUrl'=>$rowResultQuery['videoUrl']
                        ],
                        'useAR'=>intval($rowResultQuery['useAR']),
                        'arData'=>$arData
                    ];

                    if(!in_array($rowResultQuery['katalogUUID'], $arrayResult))
                    {
                        array_push($arrayResult, $arrayResult2);
                    }

                    //array_push($katalogUUIDTempArray, $rowResultQuery['katalogUUID']);
                }

                $nextPagePosition = $getPageOffset + 1;
                $nextPageUrl = base_url() . 'v1/search/query?search_query=' . $getQuerySearch . '&page=' . $nextPagePosition . '&limit=' . $getLimitOffset;

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Menampilkan hasil pencarian berdasarkan kata kunci <b>' . $getQuerySearch . '</b>', 'data'=>[
                    'katalogData'=>$arrayResult,
                    'next'=>$nextPageUrl
                ]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            else
            {
                //
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan. Oopss. Coba beberapa saat lagi ya :D', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            exit();
        }

        exit();
    }
    
    public function index_post()
	{
        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>405, 'code'=>'METHOD_NOT_ALLOWED', 'status'=>false, 'message'=>'Method Not Allowed', 'data'=>[]), 405, 'application/json; charset=UTF-8');

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