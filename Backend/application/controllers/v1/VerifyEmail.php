<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

class VerifyEmail extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
        $this->load->helper('form');
        $this->load->helper('url');
		//$this->load->helper('email');
		//header('Content-Type: application/json; charset=UTF-8');
	}
    
	public function index()
	{
        $getTokenEmail = htmlentities(trim($this->input->get('token', TRUE)), ENT_QUOTES, 'UTF-8');

        $checkToken = $this->db->query("SELECT tokenVerifikasiEmail, emailVerified FROM users_data WHERE BINARY tokenVerifikasiEmail = ? AND emailVerified <> 1", array($getTokenEmail));
        $resultCheckToken = $checkToken->result_array();

        if(count($resultCheckToken) <= 0)
        {
            $this->load->view('ConfirmEmailNotValidPage');
        }

        if(count($resultCheckToken) > 0)
        {
            $this->load->view('ConfirmEmailPage');
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
}