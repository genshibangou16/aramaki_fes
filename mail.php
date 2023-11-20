<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/mysql.php');
require_once('unpublic/elements.php');

sessionCheck();
postCheck();
csrfCheck();
genCSP();

if($_SESSION['sent']) {
	error('Multiple access', '登録確認メールを送信済みです。メールボックスをご確認ください。');
}

if(countOrder()['available'] < 9) {
    error('販売終了', '予定数の販売が終了しました。<br>申し訳ありませんが受付を終了させていただきます。');
}

$recaptchaToken = getRecaptchaToken();
$recaptchaStatus = recaptcha($recaptchaToken);

if($recaptchaStatus['score'] < 0.5) {
    error('Refused due to bot suspected', 'Reason: ' . $recaptchaStatus['reasons']);
}

$id = genUnique();
$token = genToken();
$time = time() + 3600;

function genMessage($i) {
global $sub;
$m = <<<EOM
ご来場者様

広瀬川マルシェ出店団体「GFL」です。
この度はウクライナ料理「デルニー」の事前購入サイトへご登録いただきありがとうございます。
以下のURLより購入を完了させてください。

https://$sub.sorabi.jp/purchase.php?token=$i

URLの有効期限は一時間です。
一時間以上経過した場合は再度ご登録下さい。

ご不明点等ございましたら本メールへのご返信にてご連絡ください。
本メールにお心当たりのない場合はお手数ですが破棄して頂きますようお願い致します。

皆様のご来場を心よりお待ちしております。

群馬大学GFL
https://$sub.sorabi.jp/index.php
EOM;
return $m;
}

if(isset($_POST['email'])){
	$email = $_POST['email'];
	if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $res = true;
        while($res) {
            $res = pdoDo(
                'insert into register values (?, ?, ?, from_unixtime(?));',
                [$id, $email, $token, $time],
                true
            );
            $res = $res[0];
            if($res) {
                $id = genUnique();
                $token = genToken();
            }
        }
		
		$headers = 'Content-Transfer-Encoding: 8bit'
			. PHP_EOL
			. 'From: 群馬大学GFL <sorabi.jp@gmail.com>'
			. PHP_EOL
			. 'Content-Type: text/plain; charset=utf-8';
		
		$sent = mb_send_mail(
			replaceEnter($email),
            $prefix . '【広瀬川マルシェ】 登録確認メール',
			genMessage($token),
			$headers
		);
		if($sent) {
			$_SESSION['sent'] = true;
		}
	}else {
		error('Incorrect input', 'The format of the email address is wrong.');
	}
}else {
	transit('index.php');
}

if(!$sent) {
	error('Failed to send', 'An error occurred sending mail.');
}

?>

<!doctype html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title><?=$prefix?>広瀬川マルシェ | GFL デルニー</title>
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="apple-touch-icon" href="/image/apple-touch-icon.png">
	<link rel="stylesheet" href="/style/general.css">
</head>

<body>
	<div id="wrapper">
		<div class="box">
			<h2 class="box">送信完了</h2>
			<p class="box">登録メールを送信しました。<br>メールアプリを開いて手続きを進めてください。届かない場合は迷惑メールに分類されていないかをご確認下さい。見当たらない場合は打ち間違いなどにご注意頂き再度登録してください。</p>
		</div>
	</div>
	<?=$footer?>
</body>
</html>