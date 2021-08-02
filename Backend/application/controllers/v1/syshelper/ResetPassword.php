<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

class ResetPassword extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
        $this->load->helper('form');
        $this->load->helper('url');
	}
    
	public function index()
	{
        $getTokenResetPassword = htmlentities(trim($this->input->get('token', TRUE)), ENT_QUOTES, 'UTF-8');
        $getTokenResetPasswordHash = hash('sha256', $getTokenResetPassword . $this->config->item('saltHash1'));
        $getEmailnya = htmlentities(trim($this->input->get('email', TRUE)), ENT_QUOTES, 'UTF-8');

        if(empty(trim($getTokenResetPassword)))
        {
            echo "Permintaan tidak valid!";

            exit();
        }

        if(empty(trim($getEmailnya)))
        {
            echo "Permintaan tidak valid!";

            exit();
        }

        $checkToken = $this->db->query("SELECT tokenReset, emailnya, hasUsed, expiredTimestamp FROM reset_password_data WHERE BINARY tokenReset = ? AND emailnya = ? AND hasUsed <> 1", array($getTokenResetPasswordHash, $getEmailnya));
        $resultCheckToken = $checkToken->result_array();

        if(count($resultCheckToken) <= 0)
        {
            $this->load->view('ConfirmResetPasswordNotValidPage');
        }

        if(count($resultCheckToken) > 0)
        {
            foreach($resultCheckToken as $rowToken)
            {
                $expiredTimestamp = intval($rowToken['expiredTimestamp']);
            }

            $timeNow = time();
            $selisihTimeNow = $expiredTimestamp - $timeNow;

            if($selisihTimeNow <= 0)
            {
                $this->load->view('ConfirmResetPasswordNotValidPage');
            }

            if($selisihTimeNow > 0)
            {
                $this->load->view('ConfirmResetPasswordPage');
            }
        }
    }
}