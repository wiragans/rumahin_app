<?php
$servername_db = "localhost";
$username_db = "root";
$password_db = "07b5b93c0d2063abf14bc7e9683537fbe41ab3feed916016a476e2aee04244f8";

try
{
  $conn = new PDO("mysql:host=$servername_db;dbname=rumahin_db", $username_db, $password_db);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //echo "Connected successfully";
}

catch(PDOException $e)
{
  //echo "Connection failed: " . $e->getMessage();
}
?>