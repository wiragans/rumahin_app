<?php
session_start();

require 'functions.php';
$bacaFungsi = new functions();
$baseUrlGet = $bacaFungsi->setBaseUrl();

$_SESSION['rumahinapp_admin_isLogin'] = '';
$_SESSION['rumahinapp_admin_username'] = '';
$_SESSION['rumahinapp_admin_tempNotif'] = '';
$_SESSION['rumahinapp_csrf_token'] = '';

header('Location: ' . $baseUrlGet . 'login.php');
?>