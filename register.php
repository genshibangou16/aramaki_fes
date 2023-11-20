<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/elements.php');
require_once('unpublic/mysql.php');

sessionCheck();

$message = <<<EOM
本メールアドレスが広瀬川マルシェ事前購入サイトの管理者として登録されました。
以下URLよりログインを完了させてください。

https://$sub.sorabi.jp/login.php

本メールにお心当たりのない場合はお手数ですが破棄していただきますようお願いいたします。

群馬大学GFL
https://$sub.sorabi.jp/index.php
EOM;

// email        | token    | UA      | su      | last
// varchar(256) | char(32) | char(6) | boolean | datetime

if(!isset($_SESSION['login'])) {
    transit('login.php');
}else {
    if(!$_SESSION['login']) {
        transit('login.php');
    }else {
        if(!$_SESSION['su']) {
            error('403 Forbidden', 'Lack of necessary authorization.');
        }
    }
}

if(!postCheck(true)) {
    csrfCheck();
    
    if(!isset($_POST['email'])) {
        error('Invalid access', 'An email address wasn\'t sent.<br>Please retry.');
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
    if(!$mailCheck[0]) {
        error('Duplicate registration', 'Already registered email address.');
    }
    $insert = pdoDo(
        'insert into user (email) values (?);',
        [$email],
        true
    );
    if($insert[0]) {
        error('Database failue', $insert[1]);
    }
    $headers = 'Content-Transfer-Encoding: 8bit'
  	. PHP_EOL
  	. 'From: 群馬大学GFL <sorabi.jp@gmail.com>'
  	. PHP_EOL
  	. 'Content-Type: text/plain; charset=utf-8';
    $sent = mb_send_mail(
        replaceEnter($email),
        '【広瀬川マルシェ】 管理者認証メール',
        $message,
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

$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

?>

<!doctype html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title><?=$prefix?>広瀬川マルシェ | GFL 管理者登録</title>
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="apple-touch-icon" href="/image/apple-touch-icon.png">
	<link rel="stylesheet" href="/style/general.css">
	<link rel="stylesheet" href="/style/login.css">
</head>

<body>
	<div id="wrapper">
        <form action="" class="box <?=$hide?>" method="post">
            <h2 id="form_title" class="box">登録</h2>
            <p class="box form_description">管理者として登録するメールアドレスを入力してください。</p>
            <input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
            <input class="email" type="email" name="email" placeholder="mail@example.com" id="email">
            <button type="submit" class="submit">送信</button>
        </form>
        <div class="box <?=$show?>">
            <h2 class="box">送信完了</h2>
            <p class="box">入力されたメールアドレスにログイン用のURLを送信しました。</p>
        </div>
	</div>
	<?=$footer?>
</body>
</html>