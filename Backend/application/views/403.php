<?php
header('HTTP/1.1 403 Forbidden');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode'=>403,
                'code'=>'FORBIDDEN',
				'status'=>false,
				'message'=>'Forbidden',
                'data'=>[]
                ));
return false;
?>