<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');

class Embed extends CI_Controller
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
        $getYoutubeVideoLink = htmlspecialchars(trim($this->input->get('videoUrl', TRUE)), ENT_QUOTES, 'UTF-8');
        //echo $getYoutubeVideoLink;

        if(empty(trim($getYoutubeVideoLink)))
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Parameter Link Video YouTube Diperlukan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        $parseLinkVideoKatalogYouTube = parse_url($getYoutubeVideoLink, PHP_URL_HOST);
                
        if($parseLinkVideoKatalogYouTube != "www.youtube.com" && $parseLinkVideoKatalogYouTube != "m.youtube.com")
        {
            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Mohon periksa kembali format link video YouTube-nya!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        if($parseLinkVideoKatalogYouTube == "www.youtube.com" || $parseLinkVideoKatalogYouTube == "m.youtube.com")
        {
            $videoYouTubeID = parse_url($getYoutubeVideoLink);
            parse_str($videoYouTubeID['query'], $vid);
            $getVideoYouTubeID = $vid['v'];

            if(empty(trim($getVideoYouTubeID)))
            {
                echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>false, 'message'=>'Video ID YouTube tidak valid/tidak ditemukan!', 'data'=>[]), 200, 'application/json; charset=UTF-8');

                exit();
            }

            $fixedYouTubeVideoUrl = "https://www.youtube.com/embed/" . $getVideoYouTubeID;

            echo $this->showJson(array('Content-Type: application/json; charset=UTF-8'), array('statusCode'=>200, 'status'=>true, 'message'=>'SUCCESS EMBED', 'data'=>['embeddedYoutubeVideoUrl'=>htmlspecialchars(trim($fixedYouTubeVideoUrl), ENT_QUOTES, 'UTF-8'), 'videoId'=>htmlspecialchars(trim($getVideoYouTubeID), ENT_QUOTES, 'UTF-8')]), 200, 'application/json; charset=UTF-8');

            exit();
        }

        exit();
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