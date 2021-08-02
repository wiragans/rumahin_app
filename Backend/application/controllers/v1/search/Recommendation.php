<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class Recommendation extends REST_Controller
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

            $useAdditionalPreferences = false;

            $useAdditionalPreferences = boolval($rawData['useAdditionalPreferences']);

            //var_dump($useAdditionalPreferences);

            //exit();

            if($useAdditionalPreferences == false)
            {
                //$querySQLKatalog = "SELECT * FROM katalog_data WHERE status = 1";

                // ARRAY SAW
                $getDataSAW = $this->db->query("SELECT * FROM katalog_data WHERE status = 1");
                $resultGetSAW = $getDataSAW->result_array();
            }

            if($useAdditionalPreferences == true)
            {
                $additionalPreferencesContext = $rawData['additionalPreferencesContext'];

                foreach($additionalPreferencesContext as $rowServicesContext)
                {
                    if($rowServicesContext['service'] == "REGION_PREFERENCES")
                    {
                        $enableServiceFiltering = $rowServicesContext['enableServiceFiltering'];

                        $servicesParams = $rowServicesContext['params'];

                        foreach($servicesParams as $rowServiceParams)
                        {
                            if($rowServiceParams['key'] == "provinsi_id")
                            {
                                $provinsiIDValue = $rowServiceParams['value'];
                            }

                            if($rowServiceParams['key'] == "kabupaten_id")
                            {
                                $kabupatenIDValue = $rowServiceParams['value'];
                            }

                            if($rowServiceParams['key'] == "kecamatan_id")
                            {
                                $kecamatanIDValue = $rowServiceParams['value'];
                            }

                            if($rowServiceParams['key'] == "desa_id")
                            {
                                $desaIDValue = $rowServiceParams['value'];
                            }
                        }
                    }

                    if($rowServicesContext['service'] == "CATALOG_PREFERENCES")
                    {
                        $enableServiceFiltering = $rowServicesContext['enableServiceFiltering'];

                        $servicesParams = $rowServicesContext['params'];

                        foreach($servicesParams as $rowServiceParams)
                        {
                            if($rowServiceParams['key'] == "CATALOG_TYPE_PREFERENCES")
                            {
                                $catatalogTypeValue = $rowServiceParams['value'];
                            }
                        }
                    }
                }

                // ARRAY SAW
                $getDataSAW = $this->db->query("SELECT * FROM katalog_data WHERE (BINARY kodeTipeRumah = ?) AND (provinsi_id = ? OR kabupaten_id = ? OR kecamatan_id = ? OR desa_id = ?) AND status = 1", array($catatalogTypeValue, $provinsiIDValue, $kabupatenIDValue, $kecamatanIDValue, $desaIDValue));
                $resultGetSAW = $getDataSAW->result_array();
            }

            //echo $provinsiIDValue;

            //exit();

            if(count($resultGetSAW) <= 0)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'No Data / tidak ada data katalog ditemukan. Coba atur lagi pengaturan filteringnya ya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            if(count($resultGetSAW) > 0)
            {
                // Alternatif -> Katalog UUID

                //$addMatrix = $this->addMatrixForSAW($resultGetSAW); // MEMBUAT MATRIX


                $countRaWData = sizeof($rawData['data']);

                if($countRaWData <= 0)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Invalid Request!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                    exit();
                }

                //$arrayKeysRAWData = array_keys($rawData['data']);
                //echo json_encode($arrayKeysRAWData); exit();

                $tempDataAll = array();
                $arrayKeys = array_keys($resultGetSAW[0]); // SAMPLE RESULT UNTUK DIAMBIL KEYS NYA
                
                for($loopArrayKeys = 0; $loopArrayKeys < sizeof($arrayKeys); $loopArrayKeys++)
                {
                    $arrayValueTemp = array();

                    foreach($resultGetSAW as $rowSawData)
                    {
                        $getValueArray = array_intersect_key($rowSawData, array_flip(array($arrayKeys[$loopArrayKeys])));

                        //$getValueArray = array_map('floatval', array_column($getValueArray[$arrayKeys[$loopArrayKeys]], $arrayKeys[$loopArrayKeys]));
                        $arrayValueTemp2 = $getValueArray;

                        array_push($arrayValueTemp, $arrayValueTemp2);
                    }

                    $tempDataAll2 = [
                        'attribute'=>$arrayKeys[$loopArrayKeys],
                        'tempDataArray'=>$arrayValueTemp
                    ];

                    //echo json_encode($tempDataAll2); break;

                    //$tempDataAll2 = $getValueArray;

                    array_push($tempDataAll, $tempDataAll2);

                    //break;
                }
                
                //echo json_encode($tempDataAll);

                // SORT ARRAY BY POSISI KRITERIA (PAYLOAD)
                /*for($loopArrayKeys2 = 0; $loopArrayKeys2 < sizeof($arrayKeys); $loopArrayKeys2++)
                {
                    $cariDulu = array_search($rawData['data'][$loopArrayKeys2]['criteriaAttr'], array_keys($tempDataAll));
                    //var_dump($cariDulu); break;
                }*/

                /*foreach($tempDataAll as $key => $val)
                {
                    for($loopArrayKeysPayload = 0; $loopArrayKeysPayload < sizeof($rawData['data']); $loopArrayKeysPayload++)
                    {
                        if($val['attribute'] === $rawData['data'][$loopArrayKeysPayload]['criteriaAttr'])
                        {
                            //var_dump($val);
                        }
                    }
                }

                var_dump($rawData['data']);

                exit();*/

                $addMatrix = $this->addMatrixForSAW($tempDataAll, $rawData['data'], $resultGetSAW);

                //echo json_encode($addMatrix);

                $processNormalisasi = $this->processNormalization($addMatrix, $rawData['data']);

                //echo json_encode($processNormalisasi);

                $processPerangkinganPembobotan = $this->perangkingkan($processNormalisasi, $rawData['data']);

                //echo json_encode($processPerangkinganPembobotan); // [10.487179487179487,13.15625]

                // SORT DATABASE RESULT BY RESULT PEMBOBOTAN SAW

                $arraySAWDataFix = array();

                for($sortDataBySAW = 0; $sortDataBySAW < sizeof($resultGetSAW); $sortDataBySAW++)
                {
                    $katalogID = intval($resultGetSAW[$sortDataBySAW]['id']);
                    $katalogUUIDnya = $resultGetSAW[$sortDataBySAW]['katalogUUID'];
                    $katalogName = htmlentities(trim($resultGetSAW[$sortDataBySAW]['katalogUUID']), ENT_QUOTES, 'UTF-8');
                    $katalogName = htmlentities(trim($resultGetSAW[$sortDataBySAW]['katalogName']), ENT_QUOTES, 'UTF-8');

                    $luasBanguannya = floatval($resultGetSAW[$sortDataBySAW]['luasBangunan']);
                    $luasTanahnya = floatval($resultGetSAW[$sortDataBySAW]['luasTanah']);

                    $alamatnya = $resultGetSAW[$sortDataBySAW]['alamat'];

                    $priceInt = intval($resultGetSAW[$sortDataBySAW]['harga']);
                    $priceStr = "Rp. " . number_format($priceInt,2,',','.');

                    $getImagesData = $this->db->query("SELECT * FROM katalog_images_data WHERE katalogUUID = ? ORDER BY id DESC LIMIT 1", array($katalogUUIDnya));
                    $resultGetImagesData = $getImagesData->result_array();

                    foreach($resultGetImagesData as $rowImagesData)
                    {
                        $imagesUrl = $this->config->item('baseUrlKatalogImages') . $rowImagesData['imagesUrl'];
                    }

                    $arraySAWDataFix2 = [
                        'katalogID'=>$katalogID,
                        'katalogUUID'=>$katalogUUIDnya,
                        'katalogName'=>$katalogName,
                        'thumbnailImageUrl'=>$imagesUrl,
                        'luasBangunan'=>$luasBanguannya,
                        'luasTanah'=>$luasTanahnya,
                        'alamat'=>$alamatnya,
                        'price'=>[
                            'priceInt'=>$priceInt,
                            'priceStr'=>$priceStr
                        ],
                        'bobotSAW'=>floatval($processPerangkinganPembobotan[$sortDataBySAW])
                    ];

                    array_push($arraySAWDataFix, $arraySAWDataFix2);
                }

                // SORT DARI YANG BOBOT RANGKINGNYA GEDE

                $arraySAWDataFixSort = array_column($arraySAWDataFix, 'bobotSAW');

                array_multisort($arraySAWDataFixSort, SORT_DESC, $arraySAWDataFix);

                //

                // LIMIT 10 TOP RESULTS
                $arraySAWDataFixNew = array();

                $limitResultMax = sizeof($arraySAWDataFix);

                if(sizeof($arraySAWDataFix) > 10)
                {
                    $limitResultMax = 10;
                }

                for($limitResult = 0; $limitResult < $limitResultMax; $limitResult++)
                {
                    array_push($arraySAWDataFixNew, $arraySAWDataFix[$limitResult]);
                }

                //

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>$arraySAWDataFixNew), 200, 'application/json; charset=UTF-8');

                exit();
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

    public function processNormalization($dataUntukNormalisasinya, $arrayPayloadData)
    {
        $currentAlternatifSelectedArray = array();
        $normalizedMatrixArray = array();
        $kolomLoop = 0;
        $catchDataInDifferentRow = true;

        $sortArrayPayloadDataByPositionOrder = array_column($arrayPayloadData, 'positionOrder');
        array_multisort($sortArrayPayloadDataByPositionOrder, SORT_ASC, $arrayPayloadData);

        //echo json_encode($arrayPayloadData);

        /*for($loopAlternatif = 0; $loopAlternatif < sizeof($dataUntukNormalisasinya); $loopAlternatif++)
        {
            array_push($currentAlternatifSelectedArray, $dataUntukNormalisasinya[$loopAlternatif]);
        }*/

        //exit();

        $rowLengthSample = sizeof($dataUntukNormalisasinya[0]);
        //var_dump($dataUntukNormalisasinya);
        //exit();

        //echo sizeof($dataUntukNormalisasinya); exit();

        //echo json_encode($dataUntukNormalisasinya); exit();

        while($catchDataInDifferentRow == true)
        {
            $currentAlternatifSelectedArray = [];

            for($loopAlternatif = 0; $loopAlternatif < sizeof($dataUntukNormalisasinya); $loopAlternatif++)
            {
                array_push($currentAlternatifSelectedArray, $dataUntukNormalisasinya[$loopAlternatif][$kolomLoop]);
            }

            $isBenefit = $arrayPayloadData[$kolomLoop]['isBenefit'];

            if($isBenefit == true)
            {
                $minOrMaxValue = max($currentAlternatifSelectedArray);

                for($hitungValueCount = 0; $hitungValueCount < sizeof($currentAlternatifSelectedArray); $hitungValueCount++)
                {
                    $tempValue = floatval($currentAlternatifSelectedArray[$hitungValueCount] / $minOrMaxValue);

                    if(is_nan($tempValue)) // Convert value ke 0 jika result NaN
                    {
                        $tempValue = 0;
                    }

                    $normalizedMatrixArray[$hitungValueCount][$kolomLoop] = $tempValue;
                }
            }

            if($isBenefit == false)
            {
                $minOrMaxValue = min($currentAlternatifSelectedArray);

                for($hitungValueCount = 0; $hitungValueCount < sizeof($currentAlternatifSelectedArray); $hitungValueCount++)
                {
                    $tempValue = floatval($minOrMaxValue / $currentAlternatifSelectedArray[$hitungValueCount]);

                    if(is_nan($tempValue)) // Convert value ke 0 jika result NaN
                    {
                        $tempValue = 0;
                    }

                    $normalizedMatrixArray[$hitungValueCount][$kolomLoop] = $tempValue;
                }
            }

            $kolomLoop++;

            if($kolomLoop >= $rowLengthSample)
            {
                $catchDataInDifferentRow = false;
            }
        }

        return $normalizedMatrixArray;
        // [[0.38461538461538464,1,1,1,1,0.3333333333333333,1,0.5,0.5,1,1,1,0],[1,0.96875,0.6875,1,1,1,1,1,1,1,1,0.5,0]] contoh result normalization
    }

    public function addMatrixForSAW($datanya, $payloadnya, $dataRealFromDB)
    {
        $arrayMatrix = array();
        $arrayMatrixFix = array();
        $arrayTemp = array();

        //echo count($dataRealFromDB);

        for($loopData = 0; $loopData < sizeof($datanya); $loopData++)
        {
            for($sizePayload = 0; $sizePayload < sizeof($payloadnya); $sizePayload++)
            {
                if($payloadnya[$sizePayload]['criteriaAttr'] == $datanya[$loopData]['attribute'])
                {
                    $arrayTemp2 = [
                        'attribute'=>$datanya[$loopData]['attribute'],
                        'tempDataArray'=>$datanya[$loopData]['tempDataArray'],
                        'positionOrder'=>intval($payloadnya[$sizePayload]['positionOrder'])
                        //'tempDataArray'=>array_map('floatval', $datanya[$loopData]['tempDataArray'])
                    ];

                    array_push($arrayTemp, $arrayTemp2);
                }
            }
        }

        // SORT ARRAY BY POSISI KRITERIA (PAYLOAD)

        $arrayTempSort = array_column($arrayTemp, 'positionOrder');

        array_multisort($arrayTempSort, SORT_ASC, $arrayTemp);

        //echo json_encode($arrayTemp); exit(); 
        // [{"attribute":"harga","tempDataArray":[{"harga":"6500000000"},{"harga":"2500000000"}],"positionOrder":1},{"attribute":"luasTanah","tempDataArray":[{"luasTanah":"160"},{"luasTanah":"155"}],"positionOrder":2},{"attribute":"luasBangunan","tempDataArray":[{"luasBangunan":"160"},{"luasBangunan":"110"}],"positionOrder":3},{"attribute":"jumlahKamarMandi","tempDataArray":[{"jumlahKamarMandi":"3"},{"jumlahKamarMandi":"3"}],"positionOrder":4},{"attribute":"jumlahKamarTidur","tempDataArray":[{"jumlahKamarTidur":"4"},{"jumlahKamarTidur":"4"}],"positionOrder":5},{"attribute":"jumlahRuangTamu","tempDataArray":[{"jumlahRuangTamu":"1"},{"jumlahRuangTamu":"3"}],"positionOrder":6},{"attribute":"jumlahGarasi","tempDataArray":[{"jumlahGarasi":"1"},{"jumlahGarasi":"1"}],"positionOrder":7},{"attribute":"jumlahRuangKeluarga","tempDataArray":[{"jumlahRuangKeluarga":"1"},{"jumlahRuangKeluarga":"2"}],"positionOrder":8},{"attribute":"jumlahRuangMakan","tempDataArray":[{"jumlahRuangMakan":"1"},{"jumlahRuangMakan":"2"}],"positionOrder":9},{"attribute":"jumlahGudang","tempDataArray":[{"jumlahGudang":"1"},{"jumlahGudang":"1"}],"positionOrder":10},{"attribute":"jumlahSerambi","tempDataArray":[{"jumlahSerambi":"1"},{"jumlahSerambi":"1"}],"positionOrder":11},{"attribute":"jumlahTingkat","tempDataArray":[{"jumlahTingkat":"2"},{"jumlahTingkat":"1"}],"positionOrder":12},{"attribute":"totalViewed","tempDataArray":[{"totalViewed":"0"},{"totalViewed":"0"}],"positionOrder":13}]

        //

        // CONVERT ARRAY TO MATRIX FORM SAW
        // [{"attribute":"luasTanah","tempDataArray":[{"luasTanah":"160"},{"luasTanah":"155"}]},{"attribute":"luasBangunan","tempDataArray":[{"luasBangunan":"160"},{"luasBangunan":"110"}]},{"attribute":"jumlahKamarMandi","tempDataArray":[{"jumlahKamarMandi":"3"},{"jumlahKamarMandi":"3"}]},{"attribute":"jumlahKamarTidur","tempDataArray":[{"jumlahKamarTidur":"4"},{"jumlahKamarTidur":"4"}]},{"attribute":"jumlahRuangTamu","tempDataArray":[{"jumlahRuangTamu":"1"},{"jumlahRuangTamu":"3"}]},{"attribute":"jumlahGarasi","tempDataArray":[{"jumlahGarasi":"1"},{"jumlahGarasi":"1"}]},{"attribute":"jumlahRuangKeluarga","tempDataArray":[{"jumlahRuangKeluarga":"1"},{"jumlahRuangKeluarga":"2"}]},{"attribute":"jumlahRuangMakan","tempDataArray":[{"jumlahRuangMakan":"1"},{"jumlahRuangMakan":"2"}]},{"attribute":"jumlahGudang","tempDataArray":[{"jumlahGudang":"1"},{"jumlahGudang":"1"}]},{"attribute":"jumlahSerambi","tempDataArray":[{"jumlahSerambi":"1"},{"jumlahSerambi":"1"}]},{"attribute":"jumlahTingkat","tempDataArray":[{"jumlahTingkat":"2"},{"jumlahTingkat":"1"}]},{"attribute":"harga","tempDataArray":[{"harga":"6500000000"},{"harga":"2500000000"}]},{"attribute":"totalViewed","tempDataArray":[{"totalViewed":"0"},{"totalViewed":"0"}]}]
        
        $matrixArray = array();

        for($barisCount = 0; $barisCount < sizeof($dataRealFromDB); $barisCount++)
        {
            for($kolomCount = 0; $kolomCount < sizeof($arrayTemp); $kolomCount++)
            {
                //$matrixArray2[$barisCount][$kolomCount] = $arrayTemp[$kolomCount]['tempDataArray'][$barisCount][$kolomCount]['attribute'][$barisCount];
                //echo $arrayTemp[$kolomCount]['tempDataArray'][$barisCount][$kolomCount]['attribute'][$barisCount];
                $getAttributenya = $arrayTemp[$kolomCount]['attribute'];

                $matrixArray[$barisCount][$kolomCount] = floatval($arrayTemp[$kolomCount]['tempDataArray'][$barisCount][$getAttributenya]);
                //array_push($matrixArray2[$barisCount][$kolomCount], $arrayTemp[$kolomCount]['tempDataArray'][$barisCount][$getAttributenya]);
                
            }
        }
        

        return $matrixArray;
        // [[6500000000,160,160,3,4,1,1,1,1,1,1,2,0],[2500000000,155,110,3,4,3,1,2,2,1,1,1,0]]
    }

    public function perangkingkan($normalizedMatrixArraynya, $dataPayloadnya)
    {
        $priorityArraySet = array();
        $arrayHasilPerangkingan = array();

        // mengurutkan by position order pada request body / payload

        $dataPayloadSort = array_column($dataPayloadnya, 'positionOrder');

        array_multisort($dataPayloadSort, SORT_ASC, $dataPayloadnya);

        //

        for($loopGetPriorityLevel = 0; $loopGetPriorityLevel < sizeof($dataPayloadnya); $loopGetPriorityLevel++)
        {
            $getPriorityLevelTemp = $dataPayloadnya[$loopGetPriorityLevel]['priorityLevel'];

            array_push($priorityArraySet, $getPriorityLevelTemp);
        }

        // [3,1,1,1,1,1,1,1,1,1,1,1,1] contoh array priority level

        //var_dump($priorityArraySet); exit();
        //echo json_encode($priorityArraySet); exit();

        // PROSES PERANGKINGAN

        //echo $normalizedMatrixArraynya; exit();

        for($loopBarisNormalizedArray = 0; $loopBarisNormalizedArray < sizeof($normalizedMatrixArraynya); $loopBarisNormalizedArray++)
        {
            $tempBobotAlternatifValue = 0;

            for($loopKolomNormalizedArray = 0; $loopKolomNormalizedArray < sizeof($priorityArraySet); $loopKolomNormalizedArray++)
            {
                // kolom array priority level dikalikan dengan kolom pada masing-masing baris normalized array
                $tempBobotAlternatifValue += ($priorityArraySet[$loopKolomNormalizedArray] * $normalizedMatrixArraynya[$loopBarisNormalizedArray][$loopKolomNormalizedArray]);
                //echo $tempBobotAlternatifValue;
            }

            // (0.38461538461538464 * 3) + (1 * 1) + (1 * 1) + (1 * 1) + (1 * 1) + (0.3333333333333333 * 1) + (1 * 1) + (0.5 * 1) + (0.5 * 1) + (1 * 1) + (1 * 1) + (1 * 1) + (0 * 1) <-- SAMPLE

            array_push($arrayHasilPerangkingan, $tempBobotAlternatifValue);
        }

        //

        return $arrayHasilPerangkingan;
        // [10.487179487179487,13.15625]
    }
}