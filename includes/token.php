<?php

require_once ('JWT.php');

$CONSUMER_KEY = "30d9cf1909d34daabff333acea9412c0";
$CONSUMER_SECRET = "bd9f20ea-d0da-4252-900e-9cc262b11be2";
// Expire in 1 day
$CONSUMER_TTL = 86400;

$payload = array(
	'consumerKey' => $CONSUMER_KEY,
	
	'issuedAt' => time(),
	'ttl' => $CONSUMER_TTL
);
//'userId' => 'admin',

$jwt = new JWT();
echo $jwt->encode($payload, $CONSUMER_SECRET);

?>