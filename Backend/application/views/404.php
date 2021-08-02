<?php
header('HTTP/1.1 404 Not Found');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode'=>404,
                'code'=>'NOT_FOUND',
				'status'=>false,
				'message'=>'Not Found',
                'data'=>[]
                ));
return false;
?>