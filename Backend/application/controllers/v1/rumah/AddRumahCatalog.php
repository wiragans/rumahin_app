<?php
error_reporting(0);
ini_set('max_execution_time', 300);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
date_default_timezone_set('Asia/Jakarta');

class AddRumahCatalog extends REST_Controller
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

        if(!in_array('multipart/form-data', $getContentType))
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

                $katalogName = htmlentities(trim($this->input->post('katalogName')), ENT_QUOTES, 'UTF-8');
                //$katalogDesc = htmlentities(trim($this->input->post('katalogDesc')), ENT_QUOTES, 'UTF-8');
                $katalogDesc = trim($this->input->post('katalogDesc', TRUE));
                $luasTanah = floatval($this->input->post('luasTanah'));
                $luasBangunan = floatval($this->input->post('luasBangunan'));
                $jumlahKamarMandi = intval($this->input->post('jumlahKamarMandi'));
                $jumlahKamarTidur = intval($this->input->post('jumlahKamarTidur'));
                $jumlahRuangTamu = intval($this->input->post('jumlahRuangTamu'));
                $jumlahGarasi = intval($this->input->post('jumlahGarasi'));
                $jumlahRuangKeluarga = intval($this->input->post('jumlahRuangKeluarga'));
                $jumlahRuangMakan = intval($this->input->post('jumlahRuangMakan'));
                $jumlahDapur = intval($this->input->post('jumlahDapur'));
                $jumlahGudang = intval($this->input->post('jumlahGudang'));
                $jumlahSerambi = intval($this->input->post('jumlahSerambi'));
                $jumlahTingkat = intval($this->input->post('jumlahTingkat'));
                $tahunDibuat = intval($this->input->post('tahunDibuat'));
                $harga = intval($this->input->post('harga'));
                $developerName = htmlentities(trim($this->input->post('developerName')), ENT_QUOTES, 'UTF-8');
                $developerEmail = htmlentities(trim($this->input->post('developerEmail')), ENT_QUOTES, 'UTF-8');
                $contactNumber = htmlentities(trim($this->input->post('contactNumber')), ENT_QUOTES, 'UTF-8');
                $alamat = htmlentities(trim($this->input->post('alamat')), ENT_QUOTES, 'UTF-8');
                $sertifikat = htmlentities(trim($this->input->post('sertifikat')), ENT_QUOTES, 'UTF-8');
                $isDisewakan = intval($this->input->post('isDisewakan'));
                $isSecond = intval($this->input->post('isSecond'));
                $kodeTipeRumah = $this->input->post('kodeTipeRumah');
                $modeSewa = htmlentities(trim($this->input->post('modeSewa')), ENT_QUOTES, 'UTF-8');

                $provinsiIDGet = $this->input->post('provinsi_id');
                $kabupatenIDGet = $this->input->post('kabupaten_id');
                $kecamatanIDGet = $this->input->post('kecamatan_id');
                $desaIDGet = $this->input->post('desa_id');

                $sertifikatCodeGet = $this->input->post('sertifikatCode');

                $conditionMeasurement = htmlentities(trim($this->input->post('conditionMeasurement')), ENT_QUOTES, 'UTF-8');
                $perlengkapanPerabotan = htmlentities(trim($this->input->post('perlengkapanPerabotan')), ENT_QUOTES, 'UTF-8');
                $dayaListrik = htmlentities(trim($this->input->post('dayaListrik')), ENT_QUOTES, 'UTF-8');

                $katalogVideoLink = htmlentities(trim($this->input->post('katalogVideoLink')), ENT_QUOTES, 'UTF-8');

                $useAR = intval($this->input->post('useAR'));
                $fbxFileLink = htmlentities(trim($this->input->post('fbxFileLink')), ENT_QUOTES, 'UTF-8');
                $fbxLinkDiffuseTexture = htmlentities(trim($this->input->post('fbxLinkDiffuseTexture')), ENT_QUOTES, 'UTF-8');
                $linkGambarMarker = htmlentities(trim($this->input->post('linkGambarMarker')), ENT_QUOTES, 'UTF-8');

                $fixedYouTubeVideoUrl = "";
            //

            $this->load->library('form_validation');

            $rulesForm = array([
                'field'=>'katalogName',
                'label'=>'KatalogName',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Nama Katalog rumah harus diisi dengan singkat dan jelas!')),
                ),
                ],
                [
                'field'=>'katalogDesc',
                'label'=>'KatalogDesc',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' =>json_encode(array('message'=>'Deskripsi Katalog rumah harus diisi dengan detail!')),
                ),
                ],
                [
                'field'=>'luasTanah',
                'label'=>'LuasTanah',
                'rules'=>'numeric|greater_than[0]',
                'errors' => array(
                    //'required' =>json_encode(array('message'=>'Luas tanah harus diisi!')),
                    //'decimal'=>'Luas Tanah harus format desimal atau angka'
                    'decimal'=>json_encode(array('message'=>'Luas Tanah harus format desimal atau angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang data luas tanah dengan benar'))
                ),
            ],
            [
                'field'=>'luasBangunan',
                'label'=>'LuasBangunan',
                'rules'=>'numeric|greater_than[0]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Luas Bangunan harus diisi!')),
                    //'numeric'=>'Luas Bangunan harus format desimal atau angka'
                    'numeric'=>json_encode(array('message'=>'Luas Bangunan harus format desimal atau angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang data luas bangunan dengan benar'))
                ),
            ],
            [
                'field'=>'jumlahKamarMandi',
                'label'=>'JumlahKamarMandi',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah kamar mandi harus diisi!')),
                    //'integer'=>'Jumlah kamar mandi harus format angka'
                    'integer'=>json_encode(array('message'=>'Jumlah kamar mandi harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah kamar mandi dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahKamarTidur',
                'label'=>'JumlahKamarTidur',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah kamar tidur harus diisi!')),
                    'integer'=>json_encode(array('message'=>'Jumlah kamar tidur harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah kamar tidur dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahRuangTamu',
                'label'=>'JumlahRuangTamu',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah ruang tamu harus diisi!')),
                    'integer'=>json_encode(array('message'=>'Jumlah ruang tamu harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah ruang tamu dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahGarasi',
                'label'=>'JumlahGarasi',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah garasi harus diisi!')),
                    'integer'=>json_encode(array('message'=>'Jumlah garasi harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah garasi dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahRuangKeluarga',
                'label'=>'JumlahRuangKeluarga',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah ruang keluarga harus diisi!')),
                    'integer'=>json_encode(array('message'=>'Jumlah ruang keluarga harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah ruang keluarga dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahRuangMakan',
                'label'=>'JumlahRuangMakan',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah ruang makan harus diisi!')),
                    'integer'=> json_encode(array('message'=>'Jumlah ruang makan harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah ruang makan dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahDapur',
                'label'=>'JumlahDapur',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah dapur harus diisi!')),
                    'integer'=> json_encode(array('message'=>'Jumlah dapur harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah dapur dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahGudang',
                'label'=>'JumlahGudang',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah gudang harus diisi!')),
                    'integer'=> json_encode(array('message'=>'Jumlah gudang harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah gudang dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahSerambi',
                'label'=>'JumlahSerambi',
                'rules'=>'integer|greater_than[-1]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah serambi harus diisi!')),
                    'integer'=> json_encode(array('message'=>'Jumlah serambi harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah serambi dengan angka 0 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'jumlahTingkat',
                'label'=>'JumlahTingkat',
                'rules'=>'integer|greater_than[0]',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Jumlah tingkat harus diisi!')),
                    'integer'=> json_encode(array('message'=>'Jumlah tingkat/lantai harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Isikan rumpang jumlah tingkat/lantai dengan angka 1 untuk mengosongkan'))
                ),
            ],
            [
                'field'=>'tahunDibuat',
                'label'=>'TahunDibuat',
                'rules'=>'integer|trim',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Tahun Dibuat Rumah harus diisi dengan tahun pembuatan dengan benar!')),
                    'integer'=> json_encode(array('message'=>'Tahun Dibuat Rumah harus diisi dengan tahun pembuatan dengan benar!'))
                ),
            ],
            [
                'field'=>'harga',
                'label'=>'Harga',
                'rules'=>'required|integer|greater_than[0]',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Harga jual/sewa rumah harus diisi!')),
                    'integer'=> json_encode(array('message'=>'Harga jual/sewa rumah harus format angka')),
                    'greater_than'=> json_encode(array('message'=>'Harga jual/sewa rumah tidak boleh Rp. 0'))
                ),
            ],
            [
                'field'=>'provinsi_id',
                'label'=>'Provinsi_id',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Provinsi ID harus diisi!'))
                ),
            ],
            [
                'field'=>'kabupaten_id',
                'label'=>'Kabupaten_id',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Kabupaten ID harus diisi!'))
                ),
            ],
            [
                'field'=>'kecamatan_id',
                'label'=>'Kecamatan_id',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Kecamatan ID harus diisi!'))
                ),
            ],
            [
                'field'=>'desa_id',
                'label'=>'Desa_id',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Desa/Kelurahan ID harus diisi!'))
                ),
            ],
            [
                'field'=>'developerName',
                'label'=>'DeveloperName',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Nama developer (pengembang) harus diisi!'))
                ),
            ],
            [
                'field'=>'developerEmail',
                'label'=>'DeveloperEmail',
                'rules'=>'valid_email|trim',
                'errors' => array(
                    //'required' => json_encode(array('message'=>'Email developer (pengembang) harus diisi!')),
                    'valid_email'=> json_encode(array('message'=>'Format email harus benar atau biarkan kosong untuk mengosongkan (tidak wajib diisi')),
                ),
            ],
            [
                'field'=>'contactNumber',
                'label'=>'ContactNumber',
                'rules'=>'required|trim',
                'errors' => array(
                    'required' => json_encode(array('message'=>'Contact Number Pengembang (WhatsApp Number) harus diisi!'))
                ),
            ],
            [
                'field'=>'alamat',
                'label'=>'Alamat',
                'rules'=>'required|trim',
                'errors' => array(
                    //'required' => 'Alamat rumah tinggal harus diisi dengan jelas, detail, dan valid'
                    'required'=> json_encode(array('message'=>'Alamat rumah tinggal harus diisi dengan jelas, detail, dan valid'))
                ),
            ],
            [
                'field'=>'sertifikat',
                'label'=>'Sertifikat',
                'rules'=>'trim',
                'errors' => array(
                    //'required' => 'Detail sertifikat harus diisi. Contoh: SHM - Sertifikat Hak Milik'
                    //'required'=> json_encode(array('message'=>'Detail sertifikat harus diisi. Contoh: SHM - Sertifikat Hak Milik'))
                    //'required' => 'Detail tipe sertifikat harus diisi. Contoh: SHM - Sertifikat Hak Milik'
                ),
            ],
            [
                'field'=>'sertifikatCode',
                'label'=>'SertifikatCode',
                'rules'=>'required|trim',
                'errors' => array(
                    'required'=> json_encode(array('message'=>'Tipe Sertifikat Rumah Tinggal harus diisi!'))
                ),
            ],
            [
                'field'=>'isDisewakan',
                'label'=>'IsDisewakan',
                'rules'=>'required|integer',
                'errors' => array(
                    //'required' => 'Status disewakan atau tidak harus dideklarasikan',
                    'required'=> json_encode(array('message'=>'Status disewakan atau tidak harus ditentukan')),
                    //'integer'=>'Status disewakan atau tidak harus dideklarasikan'
                    'integer'=> json_encode(array('message'=>'Status disewakan atau tidak harus ditentukan')),
                ),
            ],
            [
                'field'=>'isSecond',
                'label'=>'IsSecond',
                'rules'=>'required|integer',
                'errors' => array(
                    //'required' => 'Status baru atau second harus dideklarasikan',
                    'required'=> json_encode(array('message'=>'Status baru atau second pada rumah (properti) harus ditentukan')),
                    'integer'=> json_encode(array('message'=>'Status baru atau second pada rumah (properti) harus ditentukan')),
                ),
            ],
            [
                'field'=>'kodeTipeRumah',
                'label'=>'KodeTipeRumah',
                'rules'=>'required|trim',
                'errors' => array(
                    //'required' => 'Tipe rumah harus dideklarasikan'
                    'required' => json_encode(array('message'=>'Tipe rumah (properti) harus ditentukan'))
                ),
            ],
            [
                'field'=>'conditionMeasurement',
                'label'=>'ConditionMeasurement',
                'rules'=>'required|trim',
                'errors' => array(
                    //'required' => 'Detail sertifikat harus diisi. Contoh: SHM - Sertifikat Hak Milik'
                    'required'=> json_encode(array('message'=>'Rumpang Condition Measurement Harus diisi, misal: kalau siang agak terasa panas'))
                ),
            ],
            [
                'field'=>'perlengkapanPerabotan',
                'label'=>'PerlengkapanPerabotan',
                'rules'=>'required|trim',
                'errors' => array(
                    //'required' => 'Detail sertifikat harus diisi. Contoh: SHM - Sertifikat Hak Milik'
                    'required'=> json_encode(array('message'=>'Rumpang Perlengkapan Perabotan Harus diisi, misal: Sudah ada lemari dapur'))
                ),
            ],
            [
                'field'=>'dayaListrik',
                'label'=>'DayaListrik',
                'rules'=>'integer|greater_than[0]',
                'errors' => array(
                    //'required' => 'Detail sertifikat harus diisi. Contoh: SHM - Sertifikat Hak Milik'
                    //'required'=> json_encode(array('message'=>'Daya Listrik Harus diisi, misal: 4400 watt')),
                    'greater_than'=> json_encode(array('message'=>'Rumpang Daya Listrik Harus diisi dengan benar!')),
                ),
            ],
            [
                'field'=>'katalogVideoLink',
                'label'=>'KatalogVideoLink',
                'rules'=>'trim',
                'errors' => array(
                    //'required'=> json_encode(array('message'=>'Katalog Video Link Harus diisi dengan Link Video di YouTube')),
                ),
            ],
            [
                'field'=>'useAR',
                'label'=>'UseAR',
                'rules'=>'required|integer',
                'errors' => array(
                    'required'=> json_encode(array('message'=>'Tentukan apakah ingin menggunakan fitur AR View atau tidak!')),
                    'integer'=> json_encode(array('message'=>'Harus Format boolean (1/0)')),
                ),
            ],
            [
                'field'=>'fbxFileLink',
                'label'=>'FbxFileLink',
                'rules'=>'trim',
                'errors' => array(
                    //'required'=> json_encode(array('message'=>'Harus diisi dengan Link FBX File')),
                ),
            ],
            [
                'field'=>'fbxLinkDiffuseTexture',
                'label'=>'FbxLinkDiffuseTexture',
                'rules'=>'trim',
                'errors' => array(
                    //'required'=> json_encode(array('message'=>'Harus diisi dengan Link FBX Diffuse Texture')),
                ),
            ],
            [
                'field'=>'linkGambarMarker',
                'label'=>'LinkGambarMarker',
                'rules'=>'trim',
                'errors' => array(
                    //'required'=> json_encode(array('message'=>'Harus diisi dengan Link Gambar Marker')),
                ),
            ]
            );

            $this->form_validation->set_rules($rulesForm);

            if($this->form_validation->run())
            {
                // VALIDASI FORMAT NOMOR WHATSAPP (WA) contactNumber
                if(!empty(trim($contactNumber)))
                {
                    try
                    {
                        $numberWA = PhoneNumber::parse($contactNumber);
                        $numberWA->format(PhoneNumberFormat::INTERNATIONAL);

                        $contactNumber = $numberWA; // AMBIL DARI FORMAT NOMOR WHATSAPP YANG UDAH DIFIX ATAU DIFORMATED KE WUJUD SEBENARNYA
                    }

                    catch(PhoneNumberParseException $e)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Format nomor WhatsApp (WA) Pengembang tidak valid. Periksa kembali!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }
                }

                //$tahunDibuat = 2021;

                $isTahunDibuatValid = false;

                $isTahunDibuatValid = checkdate(1, 1, $tahunDibuat);

                if(!empty(trim($tahunDibuat)))
                {
                    if($isTahunDibuatValid == false)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Format Pengisian Tahun Rumah Dibuat Tidak Valid. Periksa lagi ya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }
                }

                if(empty(trim($tahunDibuat)))
                {
                    $tahunDibuat = "N\A";
                }

                if(!empty(trim($katalogVideoLink)))
                {
                    $parseLinkVideoKatalogYouTube = parse_url($katalogVideoLink, PHP_URL_HOST);
                    
                    if($parseLinkVideoKatalogYouTube != "www.youtube.com" && $parseLinkVideoKatalogYouTube != "m.youtube.com")
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Mohon periksa kembali format link video YouTube-nya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if($parseLinkVideoKatalogYouTube == "www.youtube.com" || $parseLinkVideoKatalogYouTube == "m.youtube.com")
                    {
                        $videoYouTubeID = parse_url($katalogVideoLink);
                        parse_str($videoYouTubeID['query'], $vid);
                        $getVideoYouTubeID = $vid['v'];

                        if(empty(trim($getVideoYouTubeID)))
                        {
                            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Video ID YouTube tidak valid/tidak ditemukan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                            exit();
                        }

                        $fixedYouTubeVideoUrl = "https://www.youtube.com/embed/" . $getVideoYouTubeID;
                    }
                }

                if($useAR == 1)
                {
                    if(!$this->validate_url($fbxFileLink)) // VALIDATE FBX FILE LINK
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'FBX File Link Tidak Valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    $fbxFileLinkDomain = parse_url($fbxFileLink, PHP_URL_HOST);
                    
                    if(empty(trim($fbxFileLinkDomain)))
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'FBX File Link Tidak Valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    // VALIDATE FBX Diffuse Texture Link
                    if(!$this->validate_url($fbxLinkDiffuseTexture))
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'FBX Diffuse Texture Link Tidak Valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    $fbxLinkDiffuseTextureDomain = parse_url($fbxLinkDiffuseTexture, PHP_URL_HOST);
                    
                    if(empty(trim($fbxLinkDiffuseTextureDomain)))
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'FBX Diffuse Texture Link Tidak Valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    // VALIDATE LINK GAMBAR MARKER
                    if(!$this->validate_url($linkGambarMarker))
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Link Gambar Marker tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    $linkGambarMarkerDomain = parse_url($linkGambarMarker, PHP_URL_HOST);
                    
                    if(empty(trim($linkGambarMarkerDomain)))
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Link Gambar Marker tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    // EXTRACT TO BASE64 ENCODED FORMAT MARKER AR
                    //$gambarMarkerBase64 = 'data:image/jpg;base64,' . base64_encode(file_get_contents($linkGambarMarker));
                    $gambarMarkerBase64 = base64_encode(file_get_contents($linkGambarMarker));
                    
                    //echo $gambarMarkerBase64;
                }

                //exit();

                //echo $katalogName;

                // VALIDASI JENIS RUMAH
                $valJenisRumah = $this->db->query("SELECT * FROM rumah_data WHERE BINARY kodeTipeRumah = ?", array($kodeTipeRumah));
                $resultValJenisRumah = $valJenisRumah->result_array();

                if(count($resultValJenisRumah) <= 0)
                {
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Kategori/Jenis Rumah Tinggal tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');
                }

                if(count($resultValJenisRumah) > 0)
                {
                    // GENERATE KATALOG UUID
                    $katalogUUID = $this->guidv4();

                    $this->load->library('upload', $this->set_image_upload_options());
                    //$jumlahGambar = count($_FILES['katalogImages']['name']);
                    
                    $dataInfo = array();
                    $arrayFileInfoImages = array();
                    $files = $_FILES;
                    //$cpt = $jumlahGambar;
                    $gambarKatalogTerupload = false;

                    for($i = 0; $i < 10; $i++)
                    {           
                            $_FILES['katalogImages']['name'] = $files['katalogImages']['name'][$i];
                            $_FILES['katalogImages']['type'] = $files['katalogImages']['type'][$i];
                            $_FILES['katalogImages']['tmp_name'] = $files['katalogImages']['tmp_name'][$i];
                            $_FILES['katalogImages']['error'] = $files['katalogImages']['error'][$i];
                            $_FILES['katalogImages']['size'] = $files['katalogImages']['size'][$i];    

                            $this->upload->initialize($this->set_image_upload_options());
                            //$this->upload->do_upload('katalogImages');
                            //$dataInfo = $this->upload->data();
                            
                            if (!$this->upload->do_upload('katalogImages')) // GAGAL
		                    {
                                //
                            }

                            else
                            {
                                $gambarKatalogTerupload = true;

                                $dataInfo = $this->upload->data();
                                $arrayFileInfoImages2 = array('file_name'=>$dataInfo['file_name'], 'file_type'=>$dataInfo['file_type'], 'raw_name'=>$dataInfo['raw_name'], 'orig_name'=>$dataInfo['orig_name'], 'client_name'=>$dataInfo['client_name'], 'file_ext'=>$dataInfo['file_ext'], 'file_size'=>floatval($dataInfo['file_size']));

                                // INSERT TO DB KATALOG IMAGES DATA
                                $imagesPath = "cdn/images/" . $dataInfo['file_name'];
                                //echo $imagesPath;
                                $insertImages = $this->db->query("INSERT INTO katalog_images_data(katalogUUID, imagesUrl, imagesFormat, fileSize) VALUES(?, ?, ?, ?)", array($katalogUUID, $imagesPath, $dataInfo['file_ext'], strval($dataInfo['file_size'])));

                                array_push($arrayFileInfoImages, $arrayFileInfoImages2);
                            }
                    }
                    
                    if($gambarKatalogTerupload == false)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Terjadi kesalahan saat upload gambar katalog rumah, periksa kembali file gambar yang Anda upload', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if($useAR != 1 && $useAR != 0)
                    {
                        $useAR = 0;
                    }

                    // Validasi PROVINSI ID, KABUPATEN ID, KECAMATAN ID, DESA ID

                    $validasiProvinsiID = $this->db->query("SELECT * FROM wilayah_provinsi WHERE id = ?", array(strval($provinsiIDGet)));
                    $resultValidasiProvinsiID = $validasiProvinsiID->result_array();

                    if(count($resultValidasiProvinsiID) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Provinsi ID tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    $validasiKabupatenID = $this->db->query("SELECT * FROM wilayah_kabupaten WHERE id = ?", array(strval($kabupatenIDGet)));
                    $resultValidasiKabupatenID = $validasiKabupatenID->result_array();

                    if(count($resultValidasiKabupatenID) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Kabupaten ID tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    $validasiKecamatanID = $this->db->query("SELECT * FROM wilayah_kecamatan WHERE id = ?", array(strval($kecamatanIDGet)));
                    $resultValidasiKecamatanID = $validasiKecamatanID->result_array();

                    if(count($resultValidasiKecamatanID) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Kecamatan ID tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    $validasiDesaID = $this->db->query("SELECT * FROM wilayah_desa WHERE id = ?", array(strval($desaIDGet)));
                    $resultValidasiDesaID = $validasiDesaID->result_array();

                    if(count($resultValidasiDesaID) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Desa/Kelurahan ID tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    // VALIDASI KODE SERTIFIKAT

                    $validasiSertifikatCode = $this->db->query("SELECT * FROM sertifikat_lists WHERE BINARY sertifikat_code = ?", array($sertifikatCodeGet));
                    $resultValidasiSertifikatCode = $validasiSertifikatCode->result_array();

                    if(count($resultValidasiSertifikatCode) <= 0)
                    {
                        echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Sertifikat katalog rumah yang dimasukkan tidak valid!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                        exit();
                    }

                    if(count($resultValidasiSertifikatCode) > 0)
                    {
                        foreach($resultValidasiSertifikatCode as $rowSertifikatCode)
                        {
                            $sertifikatName = $rowSertifikatCode['sertifikat_name'];
                            $needManualInput = intval($rowSertifikatCode['need_manual_input']);
                        }

                        if($needManualInput == 1)
                        {
                            if(empty(trim($sertifikat)))
                            {
                                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Rumpang Nama Sertifikat Harus Diisi Jika Anda memilih "LAINNYA"', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                                exit();
                            }

                            if(!empty(trim($sertifikat)))
                            {
                                $sertifikat = $sertifikatName; // REPLACE OTOMATIS SESUAI DARI DATABASE JIKA USER TIDAK MEMILIH SERTIFIKAT TYPE "LAINNYA"
                            }
                        }
                    }

                    //

                    //

                    // INSERT META DATA
                    //$tahunDibuat = date("Y");
                    $tayangTimestamp = time();
                    $totalViewed = 0;
                    $statusKatalog = 2;
                    $insertKatalog = $this->db->query("INSERT INTO katalog_data(katalogUUID, username, katalogName, katalogDesc, luasTanah, luasBangunan, jumlahKamarMandi, jumlahKamarTidur, jumlahRuangTamu, jumlahGarasi, jumlahRuangKeluarga, jumlahRuangMakan, jumlahGudang, jumlahSerambi, jumlahTingkat, harga, developerName, contactNumber, emailDeveloper, alamat, sertifikat, isDisewakan, isSecond, kodeTipeRumah, tayangTimestamp, totalViewed, modeSewa, status, tahunDibuat, useAR, sertifikat_code, provinsi_id, kabupaten_id, kecamatan_id, desa_id, jumlahDapur) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($katalogUUID, $getFixUsername, $katalogName, $katalogDesc, $luasTanah, $luasBangunan, $jumlahKamarMandi, $jumlahKamarTidur, $jumlahRuangTamu, $jumlahGarasi, $jumlahRuangKeluarga, $jumlahRuangMakan, $jumlahGudang, $jumlahSerambi, $jumlahTingkat, $harga, $developerName, $contactNumber, $developerEmail, $alamat, $sertifikat, $isDisewakan, $isSecond, $kodeTipeRumah, $tayangTimestamp, $totalViewed, $modeSewa, $statusKatalog, $tahunDibuat, $useAR, $sertifikatCodeGet, $provinsiIDGet, $kabupatenIDGet, $kecamatanIDGet, $desaIDGet, $jumlahDapur));

                    $insertMiscData = $this->db->query("INSERT INTO misc_katalog_spec(katalogUUID, tipePropertiRumah, conditionMeasurement, perlengkapanPerabotan, dayaListrik) VALUES(?, ?, ?, ?, ?)", array($katalogUUID, $kodeTipeRumah, $conditionMeasurement, $perlengkapanPerabotan, $dayaListrik));

                    //

                    $insertVideo = $this->db->query("INSERT INTO katalog_video_data(katalogUUID, videoUrl) VALUES(?, ?)", array($katalogUUID, $fixedYouTubeVideoUrl));

                    //

                    if($useAR == 1)
                    {
                        $addTimestamp = time();
                        $updateTimestamp = "";

                        $objectFileUUID = $this->guidv4();
                        $insertARData = $this->db->query("INSERT INTO ar_data(objectFileUUID, username, objectFileURL, objectFileDiffuseTextureURL, katalogUUID, addTimestamp, updateTimestamp, markerBase64Format, markerUrl) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)", array($objectFileUUID, $getFixUsername, $fbxFileLink, $fbxLinkDiffuseTexture, $katalogUUID, $addTimestamp, $updateTimestamp, $gambarMarkerBase64, $linkGambarMarker));
                    }

                    // STEMMING PROCESS

                    $baseTokenizingPerWord = str_replace(array("\t", "\r", "\n", "\r\n", "'", "-", ")", "(", "\"", "/", "=", ".", ",", ":", ";", "!", "?", ">", "<"), ' ', $katalogName . " " . $katalogDesc); // replace dengan spasi
                    $lowerCaseFoldingText = $this->caseFolding($baseTokenizingPerWord);
                    $tokenizingPerWordText = $this->tokenizing($lowerCaseFoldingText);
                    $stemmingPerWordText = $this->stemming($tokenizingPerWordText, $katalogUUID); // DECODE ARRAY DAN LAKUKAN STEMMING PENCARIAN KATA DASARNYA, DAN MASUKKAN DI DATABASE TABLE TOKEN

                    //

                    // JSON OUTPUTNYA
                    echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'Tambah Katalog Rumah Sukses. Kami perlu meninjau katalog Anda terlebih dahulu. Terima kasih :)', 'data'=>[
                        'katalogImagesArray'=>$arrayFileInfoImages,
                        'katalogUUID'=>$katalogUUID,
                        'clickTrackingParams'=>[
                            'routeTo'=>'/katalog/view/' . $katalogUUID,
                            'simpleText'=>'Klik Untuk Melihat Katalog'
                        ],
                        'textRetrieval'=>[
                            'lowerCaseFoldingText'=>$lowerCaseFoldingText,
                            'tokenizing'=>$tokenizingPerWordText,
                            'stemming'=>$stemmingPerWordText
                        ]
                    ]), 200, 'application/json; charset=UTF-8');

                    exit();
                }
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
                        'errorsMessage'=>json_decode(strip_tags(form_error($rulesForm[$ulang]['field'], '', '')), true)
                    ];

                    array_push($arrayError, $arrayError2);
                }

                //echo form_error('developerName', '<div class="error">', '</div>');

                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'code'=>'ADD_KATALOG_ERROR', 'status'=>false, 'message'=>'Permintaan tidak diterima, periksa kembali data yang diinputkan di atas!', 
                    'data'=>[
                    'errors'=>$arrayError
                    ]), 200, 'application/json; charset=UTF-8');

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

    public function set_image_upload_options()
    {   
        $config = array();
        $config['upload_path']          = realpath(APPPATH . '../cdn/images/');
        $config['allowed_types']        = 'jpg|png|jpeg';
        $config['max_size']             = 5000;
        //$config['max_width']            = 1024;
        //$config['max_height']           = 768;
        $config['max_size']             = '0';
        $config['overwrite']            = FALSE;
        $config['encrypt_name']         = FALSE;
        $config['file_name']            = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64) . time()));

        return $config;
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

    public function validate_url($urlnya)
    {
        $path = parse_url($urlnya, PHP_URL_PATH);
        $encoded_path = array_map('urlencode', explode('/', $path));
        $urlnya = str_replace($path, implode('/', $encoded_path), $urlnya);
    
        return filter_var($urlnya, FILTER_VALIDATE_URL) ? true : false;
    }

    // SOURCE BY WIRA DWI SUSANTO
    // https://github.com/wiragans/gosearch-fix

    public function caseFolding($caseFoldingData)
	{
		$loweringStringStandar = strtolower($caseFoldingData);
		return $loweringStringStandar;
    }
    
    public function tokenizing($tokenizingData)
	{
		$getTokenizingData = explode(" ", $tokenizingData); //proses awal tokenisasi, pisah dengan spasi

		// LAKUKAN STOPWORD REMOVAL JIKA ADA LIST DI BAWAH INI GUNA MEMPERCEPAT SEARCH QUERY NANTINYA
		$astoplist = array("a", "about", "above", "acara", "across", "ada", "adalah", "adanya", "after", "afterwards", "again", "against", "agar", "akan", "akhir", "akhirnya", "akibat", "aku", "all", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "amoungst", "amount", "an", "and", "anda", "another", "antara", "any", "anyhow", "anyone", "anything", "anyway", "anywhere", "apa", "apakah", "apalagi", "are", "around", "as", "asal", "at", "atas", "atau", "awal", "b", "back", "badan", "bagaimana", "bagi", "bagian", "bahkan", "bahwa", "baik", "banyak", "barang", "barat", "baru", "bawah", "be", "beberapa", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "begitu", "behind", "being", "belakang", "below", "belum", "benar", "bentuk", "berada", "berarti", "berat", "berbagai", "berdasarkan", "berjalan", "berlangsung", "bersama", "bertemu", "besar", "beside", "besides", "between", "beyond", "biasa", "biasanya", "bila", "bill", "bisa", "both", "bottom", "bukan", "bulan", "but", "by", "call", "can", "cannot", "cant", "cara", "co", "con", "could", "couldnt", "cry", "cukup", "dalam", "dan", "dapat", "dari", "datang", "de", "dekat", "demikian", "dengan", "depan", "describe", "detail", "di", "dia", "diduga", "digunakan", "dilakukan", "diri", "dirinya", "ditemukan", "do", "done", "down", "dua", "due", "dulu", "during", "each", "eg", "eight", "either", "eleven", "else", "elsewhere", "empat", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "gedung", "get", "give", "go", "had", "hal", "hampir", "hanya", "hari", "harus", "has", "hasil", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "hidup", "him", "himself", "hingga", "his", "how", "however", "hubungan", "hundred", "ia", "ie", "if", "ikut", "in", "inc", "indeed", "ingin", "ini", "interest", "into", "is", "it", "its", "itself", "itu", "jadi", "jalan", "jangan", "jauh", "jelas", "jenis", "jika", "juga", "jumat", "jumlah", "juni", "justru", "juta", "kalau", "kali", "kami", "kamis", "karena", "kata", "katanya", "ke", "kebutuhan", "kecil", "kedua", "keep", "kegiatan", "kehidupan", "kejadian", "keluar", "kembali", "kemudian", "kemungkinan", "kepada", "keputusan", "kerja", "kesempatan", "keterangan", "ketiga", "ketika", "khusus", "kini", "kita", "kondisi", "kurang", "lagi", "lain", "lainnya", "lalu", "lama", "langsung", "lanjut", "last", "latter", "latterly", "least", "lebih", "less", "lewat", "lima", "ltd", "luar", "made", "maka", "mampu", "mana", "mantan", "many", "masa", "masalah", "masih", "masing-masing", "masuk", "mau", "maupun", "may", "me", "meanwhile", "melakukan", "melalui", "melihat", "memang", "membantu", "membawa", "memberi", "memberikan", "membuat", "memiliki", "meminta", "mempunyai", "mencapai", "mencari", "mendapat", "mendapatkan", "menerima", "mengaku", "mengalami", "mengambil", "mengatakan", "mengenai", "mengetahui", "menggunakan", "menghadapi", "meningkatkan", "menjadi", "menjalani", "menjelaskan", "menunjukkan", "menurut", "menyatakan", "menyebabkan", "menyebutkan", "merasa", "mereka", "merupakan", "meski", "might", "milik", "mill", "mine", "minggu", "misalnya", "more", "moreover", "most", "mostly", "move", "much", "mulai", "muncul", "mungkin", "must", "my", "myself", "nama", "name", "namely", "namun", "nanti", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "oleh", "on", "once", "one", "only", "onto", "or", "orang", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own", "pada", "padahal", "pagi", "paling", "panjang", "para", "part", "pasti", "pekan", "penggunaan", "penting", "per", "perhaps", "perlu", "pernah", "persen", "pertama", "pihak", "please", "posisi", "program", "proses", "pula", "pun", "punya", "put", "rabu", "rasa", "rather", "re", "ribu", "ruang", "saat", "sabtu", "saja", "salah", "sama", "same", "sampai", "sangat", "satu", "saya", "sebab", "sebagai", "sebagian", "sebanyak", "sebelum", "sebelumnya", "sebenarnya", "sebesar", "sebuah", "secara", "sedang", "sedangkan", "sedikit", "see", "seem", "seemed", "seeming", "seems", "segera", "sehingga", "sejak", "sejumlah", "sekali", "sekarang", "sekitar", "selain", "selalu", "selama", "selasa", "selatan", "seluruh", "semakin", "sementara", "sempat", "semua", "sendiri", "senin", "seorang", "seperti", "sering", "serious", "serta", "sesuai", "setelah", "setiap", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "suatu", "such", "sudah", "sumber", "system", "tahu", "tahun", "tak", "take", "tampil", "tanggal", "tanpa", "tapi", "telah", "teman", "tempat", "ten", "tengah", "tentang", "tentu", "terakhir", "terhadap", "terjadi", "terkait", "terlalu", "terlihat", "termasuk", "ternyata", "tersebut", "terus", "terutama", "tetapi", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "tidak", "tiga", "tinggal", "tinggi", "tingkat", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "ujar", "umum", "un", "under", "until", "untuk", "up", "upaya", "upon", "us", "usai", "utama", "utara", "very", "via", "waktu", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "wib", "will", "with", "within", "without", "would", "ya", "yaitu", "yakni", "yang", "yet", "you", "your", "yours", "yourself", "yourselves");

		$len = count($getTokenizingData);

		//REMOVE STOPWORD DAN BUANG HASIL TOKENIZING FIELD YANG KOSONG

		$arrayTokeninizingDataFix = array();
		for($looping = 0; $looping < $len; $looping++)
		{
			if(!in_array($getTokenizingData[$looping], $astoplist) && preg_match('/\S/', $getTokenizingData[$looping]))
			{
				array_push($arrayTokeninizingDataFix, $getTokenizingData[$looping]);
			}
		}

		//

		$arrayTokeninizing = array('tokenizingCount'=>(int)$len, 'tokenizingResult'=>$arrayTokeninizingDataFix);

		return $arrayTokeninizing;
    }
    
    public function stemming($stemmingData, $katalogUUIDnya)
	{
		$stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
		$stemmer  = $stemmerFactory->createStemmer();

		$getStemmingData = $stemmingData; // INI BERUPA ARRAY DARI DATA YANG SUDAH DILAKUKAN PROSES CASE FOLDING DAN TOKENISASI SEBELUMNYA, INI AKAN DICOCOKKAN DENGAN RESULT KATA DASAR DARI DATABASE, FUNGSINYA UNTUK DILAKUKAN INPUT TOKEN DAN TOKENSTEM KE DATABASE...

        $getCurrentDokumenId = $katalogUUIDnya;
        
        $arrayStemmingFix = array();

		for($cobaUlangi = 0; $cobaUlangi < sizeof($getStemmingData['tokenizingResult']); $cobaUlangi++)
		{
			$cleanWord = preg_replace("/[^A-Za-z0-9-]/", ' ', $getStemmingData['tokenizingResult'][$cobaUlangi]);

			$outputStem = $stemmer->stem($cleanWord);

			$arrayStemmingFix2 = [
									'token'=>$cleanWord,
									'tokenstem'=>$outputStem
									];

			array_push($arrayStemmingFix, $arrayStemmingFix2);

			$updateToken = "INSERT INTO stem_token_data(katalogUUID, token, tokenstem) VALUES(?, ?, ?)";
			$doUpdateToken = $this->db->query($updateToken, array($getCurrentDokumenId, $cleanWord, $outputStem));
		}

		return $arrayStemmingFix;
	}
}