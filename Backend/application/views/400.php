<?php
header('HTTP/1.1 400 Bad Request');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode'=>400,
                'code'=>'BAD_REQUEST',
				'status'=>false,
                'message'=>'Bad Request',
                'data'=>[]
                ));
return false;
?>