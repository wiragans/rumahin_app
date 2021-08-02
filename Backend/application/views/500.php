<?php
header('HTTP/1.1 500 Internal Server Error');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode'=>500,
                'code'=>'INTERNAL_SERVER_ERROR',
				'status'=>false,
				'message'=>'Internal Server Error',
                'data'=>[]
                ));
return false;
?>