<?php
session_start();

require 'functions.php';
$bacaFungsi = new functions();
$baseUrlGet = $bacaFungsi->setBaseUrl();

if($_SESSION['rumahinapp_admin_isLogin'] == "ya")
{
    header('Location: ' . $baseUrlGet . 'dashboard.php');
}

else
{
    header('Location: ' . $baseUrlGet . 'login.php');
}
?>