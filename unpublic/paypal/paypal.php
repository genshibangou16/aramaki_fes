<?php

if($sub == 'dev') {
    $paypal = 'https://api-m.sandbox.paypal.com';
	$clientId = '********************';
	$secret = '********************';
}elseif($sub = 'gfl') {
    $paypal = 'https://api-m.paypal.com';
    $clientId = '********************';
    $secret = '********************';
}else {
    error('Illegal Access', 'Invalid host name');
}

function auth() {
    global $paypal, $clientId, $secret;
	$url = $paypal . '/v1/oauth2/token';
	$headers = array(
		'Content-Type: application/x-www-form-urlencoded'
	);
	$postData = 'grant_type=client_credentials';
	
	$currentTime = time();
	
	$currentToken = json_decode(file_get_contents(__DIR__ . '/token'));
	$issuedTime = $currentToken->issuedTime;
	$expireTime = $currentToken->token->expires_in;
    $token = $currentToken->token->access_token;
	
	if($currentTime < $issuedTime + $expireTime - 60 && !empty($token)) {
		return $token;
	}
	
	$curl = curl_init($url);
	
	curl_setopt($curl, CURLOPT_USERPWD, $clientId . ':' . $secret);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	
	$response = curl_exec($curl);
	curl_close($curl);
	
	$tokenContent = '{"issuedTime":' . $currentTime . ',"token":' . $response . '}' . PHP_EOL;
	
	file_put_contents(__DIR__ . '/token', $tokenContent);
	
	return json_decode($response)->access_token;
}

function check($id) {
    global $paypal;
	if(!ctype_alnum($id)) {
		error('Incorrect input', 'Please contact to the administrator.');
	}
	
	$accessToken = auth();
	
	$url = $paypal . '/v2/checkout/orders/' . $id;
	$headers = array(
		'Content-Type: application/json',
		'Authorization: Bearer ' . $accessToken
	);
	
	$curl = curl_init();
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	
	$response = json_decode(curl_exec($curl));
	curl_close($curl);
	
	if(isset($response->id)) {
		return array(0, $response);
	}else {
		return array(1, $response->details[0]);
	}
}
