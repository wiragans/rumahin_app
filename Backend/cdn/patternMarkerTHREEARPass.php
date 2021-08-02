<?php
$getData = $_GET['data'];
$decodeData = base64_decode($getData);
echo $decodeData;
?>