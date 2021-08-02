<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class Detail extends REST_Controller
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
                $namaLengkapnya = htmlspecialchars(trim($rowUserData['namaLengkap']), ENT_QUOTES, 'UTF-8');
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

            $getKatalogUUID = $rawData['katalogUUID'];

            sleep(2);

            //$rumahDetail = $this->db->query("SELECT katalog_data.id, katalog_data.katalogUUID, katalog_data.katalogName, katalog_data.katalogDesc, katalog_data.tayangTimestamp, katalog_data.luasTanah, katalog_data.luasBangunan, katalog_data.jumlahKamarMandi, katalog_data.jumlahKamarTidur, katalog_data.jumlahRuangTamu, katalog_data.jumlahGarasi, katalog_data.jumlahRuangKeluarga, katalog_data.jumlahRuangMakan, katalog_data.jumlahGudang, katalog_data.jumlahSerambi, katalog_data.jumlahTingkat, katalog_data.alamat, katalog_data.harga, katalog_data.developerName, katalog_data.contactNumber, katalog_data.emailDeveloper, katalog_data.tahunDibuat, katalog_data.isSecond, katalog_data.isDisewakan, katalog_data.modeSewa, katalog_data.useAR, misc_katalog_spec.tipePropertiRumah, misc_katalog_spec.conditionMeasurement, misc_katalog_spec.perlengkapanPerabotan, misc_katalog_spec.dayaListrik, katalog_video_data.videoUrl, ar_data.objectFileUUID, ar_data.objectFileURL, ar_data.objectFileDiffuseTextureURL, ar_data.markerBase64Format, ar_data.markerUrl FROM katalog_data INNER JOIN katalog_images_data ON BINARY katalog_images_data.katalogUUID = katalog_data.katalogUUID INNER JOIN katalog_video_data ON BINARY katalog_video_data.katalogUUID = katalog_data.katalogUUID INNER JOIN misc_katalog_spec ON BINARY misc_katalog_spec.katalogUUID = katalog_data.katalogUUID INNER JOIN ar_data ON BINARY ar_data.katalogUUID = katalog_data.katalogUUID WHERE BINARY katalog_data.katalogUUID = ? AND katalog_data.status = 1", array($getKatalogUUID));
            $rumahDetail = $this->db->query("SELECT * FROM katalog_data WHERE status = 1 AND BINARY katalogUUID = ?", array($getKatalogUUID));
            $resultRumahDetail = $rumahDetail->result_array();

            //var_dump($resultRumahDetail);
            //echo sizeof($resultRumahDetail);

            if(count($resultRumahDetail) <= 0)
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Katalog Rumah Tidak Ditemukan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
            }

            if(count($resultRumahDetail) > 0)
            {
                /*

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

                */

                foreach($resultRumahDetail as $rowRumahDetail)
                {
                    $provinsiIDGetDB = strval($rowRumahDetail['provinsi_id']);
                    $kabupatenIDGetDB = strval($rowRumahDetail['kabupaten_id']);
                    $kecamatanIDGetDB = strval($rowRumahDetail['kecamatan_id']);
                    $desaIDGetDB = strval($rowRumahDetail['desa_id']);

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

                    $tayangTimestamp = intval($rowRumahDetail['tayangTimestamp']);
                    $tayangTimestampReal = date("Y-m-d H:i:s", $tayangTimestamp) . " WIB";

                    $sertifikatCode = $rowRumahDetail['sertifikat_code'];
                    $sertifikatNameGet = htmlentities(trim($rowRumahDetail['sertifikat']), ENT_QUOTES, 'UTF-8');

                    $katalogID = intval($rowRumahDetail['id']);
                    $katalogUUID = $rowRumahDetail['katalogUUID'];
                    $katalogName = htmlentities(trim($rowRumahDetail['katalogName']), ENT_QUOTES, 'UTF-8');
                    $katalogDesc = $rowRumahDetail['katalogDesc'];

                    $luasTanah = floatval($rowRumahDetail['luasTanah']);
                    $luasBangunan = floatval($rowRumahDetail['luasBangunan']);
                    $jumlahKamarMandi = intval($rowRumahDetail['jumlahKamarMandi']);
                    $jumlahKamarTidur = intval($rowRumahDetail['jumlahKamarTidur']);
                    $jumlahRuangTamu = intval($rowRumahDetail['jumlahRuangTamu']);
                    $jumlahGarasi = intval($rowRumahDetail['jumlahGarasi']);
                    $jumlahRuangKeluarga = intval($rowRumahDetail['jumlahRuangKeluarga']);
                    $jumlahRuangMakan = intval($rowRumahDetail['jumlahRuangMakan']);
                    $jumlahGudang = intval($rowRumahDetail['jumlahGudang']);
                    $jumlahSerambi = intval($rowRumahDetail['jumlahSerambi']);
                    $jumlahTingkat = intval($rowRumahDetail['jumlahTingkat']);
                    $jumlahDapur = intval($rowRumahDetail['jumlahDapur']);
                    $alamat = htmlentities(trim($rowRumahDetail['alamat']), ENT_QUOTES, 'UTF-8');
                    $tahunDibuat = htmlentities(trim($rowRumahDetail['tahunDibuat']), ENT_QUOTES, 'UTF-8');

                    $developerName = htmlentities(trim($rowRumahDetail['developerName']), ENT_QUOTES, 'UTF-8');
                    $developerEmail = htmlentities(trim($rowRumahDetail['emailDeveloper']), ENT_QUOTES, 'UTF-8');
                    $developerWhatsApp = htmlentities(trim($rowRumahDetail['contactNumber']), ENT_QUOTES, 'UTF-8');

                    $totalViewed = intval($rowRumahDetail['totalViewed']);
                    $totalViewedNow = $totalViewed + 1;
                    
                    $isSecond = intval($rowRumahDetail['isSecond']);
                    $isDisewakan = intval($rowRumahDetail['isDisewakan']);

                    $modeSewa = htmlentities(trim($rowRumahDetail['modeSewa']), ENT_QUOTES, 'UTF-8');

                    if(empty(trim($modeSewa)))
                    {
                        $modeSewa = "-";
                    }

                    $priceInt = intval($rowRumahDetail['harga']);
                    $priceStr = "Rp. " . number_format($priceInt,2,',','.');

                    $useAR = intval($rowRumahDetail['useAR']);
                }

                // GET KATALOG IMAGES DATA

                $getKatalogImages = $this->db->query("SELECT * FROM katalog_images_data WHERE BINARY katalogUUID = ?", array($getKatalogUUID));
                $resultGetKatalogImages = $getKatalogImages->result_array();

                $katalogImagesData = array();

                foreach($resultGetKatalogImages as $rowKatalogImages)
                {
                    $katalogImagesData2 = [
                        'imagesUrl'=>$this->config->item('baseUrlKatalogImages') . $rowKatalogImages['imagesUrl']
                    ];

                    array_push($katalogImagesData, $katalogImagesData2);
                }

                // GET KATALOG VIDEO DATA

                $getKatalogVideo = $this->db->query("SELECT * FROM katalog_video_data WHERE BINARY katalogUUID = ?", array($getKatalogUUID));
                $resultGetKatalogVideo = $getKatalogVideo->result_array();

                $katalogVideoUrl = "";

                foreach($resultGetKatalogVideo as $rowKatalogVideo)
                {
                    $katalogVideoUrl = $rowKatalogVideo['videoUrl'];
                }

                $useYouTubeVideoUrl = 0;

                // CHECK IF KATALOG VIDEO YOUTUBE DIINPUT ATAU TIDAK DALAM KEADAAN KOSONG

                if(!empty(trim($katalogVideoUrl)))
                {
                    $useYouTubeVideoUrl = 1;
                }

                // KATALOG MISC DATA

                $getKatalogMiscData = $this->db->query("SELECT * FROM misc_katalog_spec WHERE BINARY katalogUUID = ?", array($getKatalogUUID));
                $resultGetKatalogMiscData = $getKatalogMiscData->result_array();

                $miscDataArray = array();

                foreach($resultGetKatalogMiscData as $rowMiscData)
                {
                    $getTipePropertiRumahInString = $this->db->query("SELECT * FROM rumah_data WHERE BINARY kodeTipeRumah = ?", array($rowMiscData['tipePropertiRumah']));
                    $getTipePropertiRumahInStringResult = $getTipePropertiRumahInString->result_array();

                    foreach($getTipePropertiRumahInStringResult as $rowStringPropertiRumah)
                    {
                        $tipePropertiRumahInStringReal = $rowStringPropertiRumah['tipePropertiRumahInString'];
                    }

                    $miscDataArray = [
                        'tipePropertiRumah'=>$rowMiscData['tipePropertiRumah'],
                        'tipePropertiRumahInString'=>$tipePropertiRumahInStringReal,
                        'conditionMeasurement'=>$rowMiscData['conditionMeasurement'],
                        'perlengkapanPerabotan'=>$rowMiscData['perlengkapanPerabotan'],
                        'dayaListrik'=>$rowMiscData['dayaListrik']
                    ];
                }

                // AR DATA

                if($useAR == 1)
                {
                    $getARData = $this->db->query("SELECT * FROM ar_data WHERE BINARY katalogUUID = ?", array($getKatalogUUID));
                    $resultGetARData = $getARData->result_array();

                    foreach($resultGetARData as $rowARData)
                    {
                        $objectFileUUID = $rowARData['objectFileUUID'];
                        $objectFileURL = $rowARData['objectFileURL'];
                        $objectFileDiffuseTextureURL = $rowARData['objectFileDiffuseTextureURL'];
                        $markerBase64Format = $rowARData['markerBase64Format'];
                        $markerUrl = $rowARData['markerUrl'];
                    }
                    
                    $arData = [
                        'objectFileUUID'=>$objectFileUUID,
                        'objectFileURL'=>$objectFileURL,
                        'objectFileDiffuseTextureURL'=>$objectFileDiffuseTextureURL,
                        'markerBase64Format'=>$markerBase64Format,
                        'markerUrl'=>$markerUrl
                    ];
                }
                
                if($useAR != 1)
                {
                    $arData = [
                            
                    ];
                }

                // CHECK IF THIS KATALOG IS IN THE BOOKMARK LIST

                $checkIfInBookmark = $this->db->query("SELECT * FROM bookmark_lists WHERE BINARY username = ? AND BINARY katalogUUID = ? AND isDeleted = 0", array($getFixUsername, $katalogUUID));
                $resultCheckIfInBookmark = $checkIfInBookmark->result_array();

                $isThisKatalogBookmarked = 0;
                $bookmarkListingID = null;

                if(count($resultCheckIfInBookmark) > 0)
                {
                    foreach($resultCheckIfInBookmark as $rowBookmarkData)
                    {
                        $bookmarkListingID = intval($rowBookmarkData['id']);
                    }

                    $isThisKatalogBookmarked = 1;
                }

                // CHECK SERTIFIKAT TYPE NYA

                $sertifikatTypeCheck = $this->db->query("SELECT * FROM sertifikat_lists WHERE BINARY sertifikat_code = ?", array($sertifikatCode));
                $resultSertifikatTypeCheck = $sertifikatTypeCheck->result_array();

                $sertifikatNameFix = "";

                if(count($resultSertifikatTypeCheck) > 0)
                {
                    foreach($resultSertifikatTypeCheck as $rowSertifikatTypeCheck)
                    {
                        $sertifikatCodeFix = $rowSertifikatTypeCheck['sertifikat_code'];
                        $sertifikatName = $rowSertifikatTypeCheck['sertifikat_name'];
                        $needManualInputSertifikat = intval($rowSertifikatTypeCheck['need_manual_input']);
                    }

                    if($needManualInputSertifikat == 1)
                    {
                        $sertifikatNameFix = $sertifikatCodeFix . " - " . $sertifikatNameGet;
                    }

                    if($needManualInputSertifikat != 1)
                    {
                        $sertifikatNameFix = $sertifikatCodeFix . " - " . $sertifikatName;
                    }
                }

                if(count($resultSertifikatTypeCheck) <= 0)
                {
                    $sertifikatNameFix = "-";
                }

                //

                // RESULT JSON

                if(empty(trim($developerEmail)))
                {
                    $developerEmail = "-";
                }

                $arrayResult = [
                    'katalogID'=>$katalogID,
                    'tayangTimestamp'=>$tayangTimestamp,
                    'tayangTimestampReal'=>$tayangTimestampReal,
                    'katalogUUID'=>$katalogUUID,
                    'katalogName'=>$katalogName,
                    'katalogDesc'=>$katalogDesc,
                    'totalViewed'=>$totalViewedNow,
                    'isThisKatalogBookmarked'=>$isThisKatalogBookmarked,
                    'bookmarkListingID'=>$bookmarkListingID,
                    'details'=>[
                        'luasTanah'=>$luasTanah,
                        'luasBangunan'=>$luasBangunan,
                        'jumlahKamarMandi'=>$jumlahKamarMandi,
                        'jumlahKamarTidur'=>$jumlahKamarTidur,
                        'jumlahRuangTamu'=>$jumlahRuangTamu,
                        'jumlahGarasi'=>$jumlahGarasi,
                        'jumlahRuangKeluarga'=>$jumlahRuangKeluarga,
                        'jumlahRuangMakan'=>$jumlahRuangMakan,
                        'jumlahDapur'=>$jumlahDapur,
                        'jumlahGudang'=>$jumlahGudang,
                        'jumlahSerambi'=>$jumlahSerambi,
                        'jumlahTingkat'=>$jumlahTingkat,
                        'fixedAlamat'=>$desannya . ', ' . $kecamatannya . ', ' . $kabupatennya . ', ' . $provinsinya,
                        'alamat'=>$alamat,
                        'tahunDibuat'=>$tahunDibuat,
                        'sertifikat'=>$sertifikatNameFix
                    ],
                    'miscDetails'=>$miscDataArray,
                    'developerInfo'=>[
                        'developerName'=>$developerName,
                        'developerEmail'=>$developerEmail,
                        'developerWhatsApp'=>[
                            'number'=>$developerWhatsApp,
                            'clickTrackingParams'=>[
                                'simpleText'=>'Hubungi melalui WhatsApp',
                                'href'=>'https://wa.me/' . $developerWhatsApp . '?text=' . urlencode('Halo, sebelumnya perkenalkan nama saya ' . $namaLengkapnya . '. Saya ingin menanyakan seputar ' . $katalogName . '. Terima kasih 😊')
                            ]
                        ]
                    ],
                    'isSecond'=>$isSecond,
                    'isDisewakan'=>$isDisewakan,
                    'modeSewa'=>$modeSewa,
                    'price'=>[
                        'priceInt'=>$priceInt,
                        'priceStr'=>$priceStr
                    ],
                    'katalogImagesData'=>$katalogImagesData,
                    'useYouTubeVideoUrl'=>$useYouTubeVideoUrl,
                    'katalogVideoData'=>[
                        'videoUrl'=>$katalogVideoUrl
                    ],
                    'useAR'=>$useAR,
                    'arData'=>$arData
                ];

                $updateTotalView = $this->db->query("UPDATE katalog_data SET totalViewed = ? WHERE BINARY katalogUUID = ?", array($totalViewedNow, $katalogUUID));

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS', 'data'=>$arrayResult), 200, 'application/json; charset=UTF-8');

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
}