<?php

$sub = explode('.', $_SERVER['HTTP_HOST'])[0];

if($sub == 'dev') {
    $prefix = '【テスト】 ';
}else {
    $prefix = '';
}

function genCSP($i = false) {
	$csp = "Content-Security-Policy: default-src 'self'";
	if($i) {
		$csp = $csp . '; ' . $i;
	}
	header($csp);
}

function genUnique() {
	return substr(base64_encode(hash('crc32b', uniqid(mt_rand(), true), true)), 0, 6);
}

function genToken() {
	return bin2hex(openssl_random_pseudo_bytes(16));
}

function replaceHtml($i) {
	return htmlspecialchars($i, ENT_QUOTES, 'UTF-8');
}

function replaceEnter($i) {
	return str_replace(array('\r\n', '\r', '\n'), '', $i);
}

function transit($i) {
	header('Location: ' . $i, true, 302);
	exit();
}

function error($i, $j = null) {
	$_SESSION['error'] = $i;
	if($j) {
		$_SESSION['errorMessage'] = $j;
	}else {
        $_SESSION['errorMessage'] = '';
    }
	transit('error.php');
}

function postCheck($i = 0) {
	if(empty($_POST)) {
		if($i) {
			return 1;
		}else {
			error('Illegal Access', '[POST]<br>Please start again from the top page.');
		}
	}
}

function getCheck($i = 0) {
	if(empty($_GET)) {
		if($i) {
			return 1;
		}else {
			error('Illegal Access', '[GET]<br>Please start again from the top page.');
		}
	}
}

function sessionCheck($i = 0) {
	if(!isset($_SESSION)) {
		if($i) {
			return 1;
		}else {
			header('Content-Type: text/plain');
			echo 'Error: Please check your browser\'s Cookie settings.';
			exit();
		}
	}
}

function csrfCheck($i = 0) {
	if(!isset($_SESSION['csrfToken']) || !isset($_POST['csrfToken'])) {
		if($i) {
			return 1;
		}else {
			error('Illegal Access', 'token not set');
		}
	}
	if($_SESSION['csrfToken'] !== $_POST['csrfToken']) {
		if($i) {
			return 1;
		}else {
			error('Illegal Access', 'token mismatch');
		}
	}
}

function getRecaptchaToken() {
    if(isset($_POST['g-recaptcha-response'])) {
        return $_POST['g-recaptcha-response'];
    }else {
        error('Recaptcha Error', 'Failed to load recaptcha.<br>Please contact administrator.');
    }
}

function recaptcha($i) {
    $url = 'https://recaptchaenterprise.googleapis.com/v1/projects/sonic-terminal-352012/assessments';
    $key = '6LffPhwjAAAAAK656k0ij7GlHQLaBY5OUARrgjNF';
    $data = array(
        'event' => array(
            'token' => $i,
            'siteKey' => $key
        )
    );
    $token = file_get_contents('unpublic/gcloud/token');
    $headers = array(
		'Content-Type: application/json; charset=utf-8',
        'Authorization: Bearer ' . trim($token)
	);
    $curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $res = json_decode(curl_exec($curl));
    curl_close($curl);
    if(!$res->tokenProperties->valid) {
        error('Invalid Recaptcha token', $res->tokenProperties->invalidReason);
    }
    $assessment = array(
        'score' => $res->riskAnalysis->score,
        'reasons' => implode(',', $res->riskAnalysis->reasons)
    );
    return $assessment;
}

function loginCheck() {
    if(!isset($_SESSION['login'])) {
        transit('login.php');
    }
    if(!$_SESSION['login']) {
        transit('login.php');
    }
}