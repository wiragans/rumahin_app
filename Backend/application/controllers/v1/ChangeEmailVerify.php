<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

class ChangeEmailVerify extends CI_Controller
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
        $getTokenChangeEmail = htmlentities(trim($this->input->get('token', TRUE)), ENT_QUOTES, 'UTF-8');
        $getTokenChangeEmailHash = hash('sha256', $getTokenChangeEmail . $this->config->item('saltHash1'));

        $checkToken = $this->db->query("SELECT token, hasUsed, canNotBeUsed FROM ganti_email_data WHERE BINARY token = ? AND hasUsed = 0 AND canNotBeUsed = 0", array($getTokenChangeEmailHash));
        $resultCheckToken = $checkToken->result_array();

        if(count($resultCheckToken) <= 0)
        {
            echo "<b>Token sudah tidak berlaku lagi atau kedaluwarsa!</b>";
        }

        if(count($resultCheckToken) > 0)
        {
            $this->load->view('ChangeEmailVerifyPage');
        }
    }
}