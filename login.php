<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/elements.php');
require_once('unpublic/mysql.php');

sessionCheck();
genCSP("script-src www.google.com www.gstatic.com 'self'; frame-src www.google.com 'self'");

function genMessage($i) {
global $sub;
$m = <<<EOM
本メールアドレスにて広瀬川マルシェ事前購入サイト管理者ページへのログインが試行されました。
意図した操作である場合は以下URLよりログインを完了させてください。

https://$sub.sorabi.jp/login.php?token=$i

本メールにお心当たりのない場合はお手数ですが破棄していただきますようお願いいたします。

群馬大学GFL
https://$sub.sorabi.jp/index.php
EOM;
return $m;
}

// email        | token    | UA      | su      | last
// varchar(256) | char(32) | char(6) | boolean | datetime

if(isset($_SESSION['login'])) {
    if($_SESSION['login']) {
        transit('manage.php');
    }
}

$ua = substr(base64_encode(hash('crc32', $_SERVER['HTTP_USER_AGENT'], true)), 0, 6);

function tokenChack($token, $useragent, $isCookie = false) {
    $status = pdoDo(
        'select email, ua, su, expire, token from user where token = ?',
        [$token],
        true,
        true
    );
    if($status[0] && $isCookie) {
        setcookie('token', '', time() - 3600);
        if($status[1]) {
            error('Database failue', $status[1]);
        }else {
            transit('login.php');
        }
    }elseif($status[0]) {
        if($status[1]) {
            error('Database failue', $status[1]);
        }else {
            error('Invalid Token', 'Please check you access the correct URL.');
        }
    }else {
        $status = $status[1];
        if(strtotime($status['expire']) < time() || $status['ua'] != $useragent) {
            if($isCookie) {
                setcookie('token', '', time() - 3600);
            }else {
                error('Login failue', 'May be accessing from different devices.');
            }
        }else {
            $_SESSION['login'] = true;
            if($status['su']) {
                $_SESSION['su'] = true;
            }else {
                $_SESSION['su'] = false;
            }
            $expire = time() + 60 * 60 * 24;
            $flag = true;
            while($flag) {
                $tokenNew = genToken();
                $update = pdoDo(
                    'update user set token = ?, expire = from_unixtime(?) where token = ?;',
                    [$tokenNew, $expire, $status['token']],
                    true
                );
                $flag = $update[0];
            }
            setcookie('token', $tokenNew, $expire);
            transit('manage.php');
        }
    }
}

if(isset($_COOKIE['token'])) {
    tokenChack($_COOKIE['token'], $ua, true);
}else {
    if(isset($_GET['token'])) {
        tokenChack($_GET['token'], $ua);
    }else {
        if(!postCheck(true)) {
            csrfCheck();
            
            $recaptchaToken = getRecaptchaToken();
            $recaptchaStatus = recaptcha($recaptchaToken);
            if($recaptchaStatus['score'] < 0.5) {
                error('Refused due to bot suspected', 'Reason: ' . $recaptchaStatus['reasons']);
            }
            
            if(!isset($_POST['email'])) {
                error('Invalid access', 'Please retry.');
            }else {
                $email = $_POST['email'];
                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    error('Invalid access', 'Wrong email address.<br>Please retry.');
                }
            }
            $mailCheck = pdoDo(
                'select * from user where email = ?;',
                [$email],
                true,
                true
            );
            if($mailCheck[0]) {
                if($mailCheck[1]) {
                    error('Database failue', $mailCheck[1]);
                }else {
                    error('Unregistered email address', 'Please contact the administrator.');
                }
            }
            $flag = true;
            while($flag) {
                $token = genToken();
                $insert = pdoDo(
                    'update user set token = ?, ua = ?, expire = from_unixtime(?) where email = ?;',
                    [$token, $ua, time() + 86400, $email],
                    true
                );
                $flag = $insert[0];
            }
            $headers = 'Content-Transfer-Encoding: 8bit'
		  	. PHP_EOL
		  	. 'From: 群馬大学GFL <sorabi.jp@gmail.com>'
		  	. PHP_EOL
		  	. 'Content-Type: text/plain; charset=utf-8';
            $sent = mb_send_mail(
                replaceEnter($email),
                '【広瀬川マルシェ】 ログイン認証メール',
                genMessage($token),
                $headers
            );
            if($sent) {
                $hide = 'hidden';
                $show = '';
            }else {
                error('Failed to send the mail', 'Please contact the administrator.');
            }
        }else {
            $hide = '';
            $show = 'hidden';
        }
    }
}

$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

?>

<!doctype html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title><?=$prefix?>広瀬川マルシェ | GFL ログイン</title>
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="apple-touch-icon" href="/image/apple-touch-icon.png">
	<link rel="stylesheet" href="/style/general.css">
	<link rel="stylesheet" href="/style/login.css">
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6LffPhwjAAAAAK656k0ij7GlHQLaBY5OUARrgjNF"></script>
</head>

<body>
	<div id="wrapper">
        <form action="" class="box <?=$hide?>" method="post" id="form">
            <h2 id="form_title" class="box">ログイン</h2>
            <p class="box form_description">関係者向けのログインページです。来場者の方はお送りした注文完了メールから注文内容をご確認ください。<br>関係者は登録済みのメールアドレスを入力してください。</p>
            <input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
            <input class="email" type="email" name="email" placeholder="mail@example.com" id="email">
            <div>
        	    <button class="submit g-recaptcha"
                        type="submit"
                        data-sitekey="6LffPhwjAAAAAK656k0ij7GlHQLaBY5OUARrgjNF"
                        data-callback='onSubmit'
                        data-action='submit'>送信</button>
            </div>
        </form>
        <div class="box <?=$show?>">
            <h2 class="box">送信完了</h2>
            <p class="box">入力されたメールアドレスにログイン用のURLを送信しました。</p>
        </div>
	</div>
	<?=$footer?>
    <script src="/script/login.js"></script>
</body>
</html>