<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class PopularKatalogMore extends REST_Controller
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

            // popular katalog get more
            $getPageOffset = intval($this->input->get('page'));
            $getLimitOffset = intval($this->input->get('limit'));

            $useFiltering = intval($this->input->get('enableFiltering', TRUE));
            $filteringData = $this->input->get('filteringData', TRUE);

            //echo base64_decode($filteringData); exit();
            // {"context":[{"service":"REGION_PREFERENCES","enableServiceFiltering":true,"params":[{"key":"provinsi_id","value":"33"},{"key":"kabupaten_id","value":"3318"},{"key":"kecamatan_id","value":"3318120"},{"key":"desa_id","value":"3318120018"}]},{"service":"CATALOG_PREFERENCES","enableServiceFiltering":true,"params":[{"key":"CATALOG_TYPE_PREFERENCES","value":"detached"}]}]}

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

            //
            if($useFiltering == 1)
            {
                $filteringDataBase64Decode = base64_decode($filteringData);
                $filteringDataBase64DecodeParseJson = json_decode($filteringDataBase64Decode, true);

                $provinsiIDGet = "";
                $kabupatenIDGet = "";
                $kecamatanIDGet = "";
                $desaIDGet = "";

                //var_dump($filteringDataBase64DecodeParseJson); exit();

                if(is_null($filteringDataBase64DecodeParseJson) || $filteringDataBase64DecodeParseJson == "")
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Permintaan tidak valid!', 'data'=>[
                    ]), 200, 'application/json; charset=UTF-8');
                    
                    exit();
                }

                $regionPreferencesSuccess = true;
                $catalogTypePreferencesSuccess = true;

                foreach($filteringDataBase64DecodeParseJson['context'] as $rowFilterData)
                {
                    if($rowFilterData['service'] == "REGION_PREFERENCES")
                    {
                        $enableServiceFiltering = false;
                        $enableServiceFiltering = $rowFilterData['enableServiceFiltering'];

                        if(is_nan($enableServiceFiltering) || is_null($enableServiceFiltering))
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Permintaan tidak valid!', 'data'=>[
                                ]), 200, 'application/json; charset=UTF-8');
                                
                            exit();
                        }

                        if($enableServiceFiltering == true)
                        {
                            // CHECK DAERAH
                            foreach($rowFilterData['params'] as $rowParamsData)
                            {
                                if($rowParamsData['key'] == "provinsi_id")
                                {
                                    $validateParams = $this->db->query("SELECT * FROM wilayah_provinsi WHERE id = ?", array(strval($rowParamsData['value'])));
                                    $resultValidateParams = $validateParams->result_array();

                                    //var_dump($resultValidateParams);

                                    if(count($resultValidateParams) <= 0)
                                    {
                                        $regionPreferencesSuccess = false;

                                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                            'katalogData'=>[],
                                            'next'=>'',
                                        ]), 200, 'application/json; charset=UTF-8');
                                        
                                        exit();
                                    }

                                    if(count($resultValidateParams) > 0)
                                    {
                                        $provinsiIDGet = strval($rowParamsData['value']);
                                    }
                                }

                                else if($rowParamsData['key'] == "kabupaten_id")
                                {
                                    $validateParams = $this->db->query("SELECT * FROM wilayah_kabupaten WHERE id = ?", array(strval($rowParamsData['value'])));
                                    $resultValidateParams = $validateParams->result_array();

                                    if(count($resultValidateParams) <= 0)
                                    {
                                        $regionPreferencesSuccess = false;

                                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                            'katalogData'=>[],
                                            'next'=>'',
                                        ]), 200, 'application/json; charset=UTF-8');
                                        
                                        exit();
                                    }

                                    if(count($resultValidateParams) > 0)
                                    {
                                        $kabupatenIDGet = strval($rowParamsData['value']);
                                    }
                                }

                                else if($rowParamsData['key'] == "kecamatan_id")
                                {
                                    $validateParams = $this->db->query("SELECT * FROM wilayah_kecamatan WHERE id = ?", array(strval($rowParamsData['value'])));
                                    $resultValidateParams = $validateParams->result_array();

                                    if(count($resultValidateParams) <= 0)
                                    {
                                        $regionPreferencesSuccess = false;

                                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                            'katalogData'=>[],
                                            'next'=>'',
                                        ]), 200, 'application/json; charset=UTF-8');
                                        
                                        exit();
                                    }

                                    if(count($resultValidateParams) > 0)
                                    {
                                        $kecamatanIDGet = strval($rowParamsData['value']);
                                    }
                                }

                                else if($rowParamsData['key'] == "desa_id")
                                {
                                    $validateParams = $this->db->query("SELECT * FROM wilayah_desa WHERE id = ?", array(strval($rowParamsData['value'])));
                                    $resultValidateParams = $validateParams->result_array();

                                    if(count($resultValidateParams) <= 0)
                                    {
                                        $regionPreferencesSuccess = false;

                                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                            'katalogData'=>[],
                                            'next'=>'',
                                        ]), 200, 'application/json; charset=UTF-8');
                                        
                                        exit();
                                    }

                                    if(count($resultValidateParams) > 0)
                                    {
                                        $desaIDGet = strval($rowParamsData['value']);
                                    }
                                }

                                else
                                {
                                    $regionPreferencesSuccess = false;

                                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                        'katalogData'=>[],
                                        'next'=>'',
                                    ]), 200, 'application/json; charset=UTF-8');
                                    
                                    exit();
                                }
                            }
                        }
                    }

                    if($rowFilterData['service'] == "CATALOG_PREFERENCES")
                    {
                        $enableServiceFiltering = false;
                        $enableServiceFiltering = $rowFilterData['enableServiceFiltering'];

                        if(is_nan($enableServiceFiltering) || is_null($enableServiceFiltering))
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Permintaan tidak valid!', 'data'=>[
                                ]), 200, 'application/json; charset=UTF-8');
                                
                            exit();
                        }

                        if($enableServiceFiltering == true)
                        {
                            // CHECK DAERAH
                            foreach($rowFilterData['params'] as $rowParamsData)
                            {
                                if($rowParamsData['key'] == "CATALOG_TYPE_PREFERENCES")
                                {
                                    $validateParams = $this->db->query("SELECT * FROM rumah_data WHERE BINARY kodeTipeRumah = ?", array(strval($rowParamsData['value'])));
                                    $resultValidateParams = $validateParams->result_array();

                                    //var_dump($resultValidateParams);

                                    if(count($resultValidateParams) <= 0)
                                    {
                                        $catalogTypePreferencesSuccess = false;

                                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                            'katalogData'=>[],
                                            'next'=>'',
                                        ]), 200, 'application/json; charset=UTF-8');
                                        
                                        exit();
                                    }

                                    if(count($resultValidateParams) > 0)
                                    {
                                        $catalogTypeGet = strval($rowParamsData['value']);
                                    }
                                }

                                else
                                {
                                    $catalogTypePreferencesSuccess = false;

                                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                                        'katalogData'=>[],
                                        'next'=>'',
                                    ]), 200, 'application/json; charset=UTF-8');
                                    
                                    exit();
                                }
                            }
                        }
                    }
                }
            }

            //var_dump($catalogTypePreferencesSuccess);

            //exit();
            //

            $startFrom = intval(($getPageOffset - 1) * $getLimitOffset);

            if($useFiltering == 1)
            {
                if($regionPreferencesSuccess == true && $catalogTypePreferencesSuccess == true)
                {
                    $getPopularKatalog = $this->db->query("SELECT * FROM katalog_data WHERE status = 1 AND provinsi_id = ? AND kabupaten_id = ? AND kecamatan_id = ? AND desa_id = ? AND BINARY kodeTipeRumah = ? ORDER BY totalViewed DESC LIMIT ?, ?", array($provinsiIDGet, $kabupatenIDGet, $kecamatanIDGet, $desaIDGet, $catalogTypeGet, intval($startFrom), intval($getLimitOffset)));
                    $resultGetPopularKatalog = $getPopularKatalog->result_array();
                }

                if($regionPreferencesSuccess == true && $catalogTypePreferencesSuccess == false)
                {
                    $getPopularKatalog = $this->db->query("SELECT * FROM katalog_data WHERE status = 1 AND provinsi_id = ? AND kabupaten_id = ? AND kecamatan_id = ? AND desa_id = ? ORDER BY totalViewed DESC LIMIT ?, ?", array($provinsiIDGet, $kabupatenIDGet, $kecamatanIDGet, $desaIDGet, intval($startFrom), intval($getLimitOffset)));
                    $resultGetPopularKatalog = $getPopularKatalog->result_array();
                }

                if($regionPreferencesSuccess == false && $catalogTypePreferencesSuccess == true)
                {
                    $getPopularKatalog = $this->db->query("SELECT * FROM katalog_data WHERE status = 1 AND BINARY kodeTipeRumah = ? ORDER BY totalViewed DESC LIMIT ?, ?", array($catalogTypeGet, intval($startFrom), intval($getLimitOffset)));
                    $resultGetPopularKatalog = $getPopularKatalog->result_array();
                }
            }

            if($useFiltering != 1)
            {
                $getPopularKatalog = $this->db->query("SELECT * FROM katalog_data WHERE status = 1 ORDER BY totalViewed DESC LIMIT ?, ?", array(intval($startFrom), intval($getLimitOffset)));
                $resultGetPopularKatalog = $getPopularKatalog->result_array();
            }

            $nextPage = $getPageOffset + 1;
            $nextUrl = base_url() . 'v1/front/more/popularKatalog?page=' . $nextPage . '&limit=' . $getLimitOffset;

            $arrayKatalogList = array();

            foreach($resultGetPopularKatalog as $rowPopularKatalog)
            {
                $getKatalogImages = $this->db->query("SELECT * FROM katalog_images_data WHERE BINARY katalogUUID = ? ORDER BY id ASC LIMIT 1", array($rowPopularKatalog['katalogUUID']));
                $resultGetKatalogImages = $getKatalogImages->result_array();

                foreach($resultGetKatalogImages as $rowKatalogImages)
                {
                    $katalogImageUrl = $this->config->item('baseUrlKatalogImages') . $rowKatalogImages['imagesUrl'];
                }

                $provinsiIDGetDB = strval($rowPopularKatalog['provinsi_id']);
                $kabupatenIDGetDB = strval($rowPopularKatalog['kabupaten_id']);
                $kecamatanIDGetDB = strval($rowPopularKatalog['kecamatan_id']);
                $desaIDGetDB = strval($rowPopularKatalog['desa_id']);

                $getFixedAlamatProvinsi = $this->db->query("SELECT * FROM wilayah_provinsi WHERE id = ?", array($provinsiIDGetDB));
                $resultGetFixedAlamatProvinsi = $getFixedAlamatProvinsi->result_array();

                $getFixedAlamatKabupaten = $this->db->query("SELECT * FROM wilayah_kabupaten WHERE id = ?", array($kabupatenIDGetDB));
                $resultGetFixedAlamatKabupaten = $getFixedAlamatKabupaten->result_array();

                $getFixedAlamatKecamatan = $this->db->query("SELECT * FROM wilayah_kecamatan WHERE id = ?", array($kecamatanIDGetDB));
                $resultGetFixedAlamatKecamatan = $getFixedAlamatKecamatan->result_array();

                $getFixedAlamatDesa = $this->db->query("SELECT * FROM wilayah_desa WHERE id = ?", array($desaIDGetDB));
                $resultGetFixedAlamatDesa = $getFixedAlamatDesa->result_array();

                foreach($resultGetFixedAlamatProvinsi as $rowResult1)
                {
                    $provinsinya = $rowResult1['nama'];
                }

                foreach($resultGetFixedAlamatKabupaten as $rowResult2)
                {
                    $kabupatennya = $rowResult2['nama'];
                }

                foreach($resultGetFixedAlamatKecamatan as $rowResult3)
                {
                    $kecamatannya = $rowResult3['nama'];
                }

                foreach($resultGetFixedAlamatDesa as $rowResult4)
                {
                    $desannya = $rowResult4['nama'];
                }

                $arrayKatalogList2 = [
                    'id'=>intval($rowPopularKatalog['id']),
                    'katalogUUID'=>$rowPopularKatalog['katalogUUID'],
                    'katalogName'=>htmlentities(trim($rowPopularKatalog['katalogName']), ENT_QUOTES, 'UTF-8'),
                    'katalogDesc'=>$rowPopularKatalog['katalogDesc'],
                    'fixedAlamat'=>$desannya . ', ' . $kecamatannya . ', ' . $kabupatennya . ', ' . $provinsinya,
                    'alamat'=>$rowPopularKatalog['alamat'],
                    'totalViewed'=>intval($rowPopularKatalog['totalViewed']),
                    'luasBangunan'=>floatval($rowPopularKatalog['luasBangunan']),
                    'luasTanah'=>floatval($rowPopularKatalog['luasTanah']),
                    'priceInt'=>intval($rowPopularKatalog['harga']),
                    'priceStr'=>"Rp. " . number_format(intval($rowPopularKatalog['harga']),2,',','.'),
                    'publishedAt'=>date("Y-m-d H:i:s", $rowPopularKatalog['tayangTimestamp']) . ' WIB',
                    'katalogImageUrl'=>$katalogImageUrl
                ];

                array_push($arrayKatalogList, $arrayKatalogList2);
            }

            sleep(1);

            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>[
                'katalogData'=>$arrayKatalogList,
                'next'=>$nextUrl
            ]), 200, 'application/json; charset=UTF-8');

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