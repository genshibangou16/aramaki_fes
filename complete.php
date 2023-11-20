<?php

session_start();
include_once('unpublic/func.php');
include_once('unpublic/elements.php');

sessionCheck();
getCheck();
genCSP();

if(!isset($_GET['csrfToken']) || !isset($_SESSION['csrfToken'])) {
	error('Illegal Access', 'Token not set.');
}

if($_GET['csrfToken'] != $_SESSION['csrfToken']) {
	error('Illegal Access', 'Incorrect token.');
}

if($_SESSION['sent']) {
	error('Multiple access', 'The mail was already sent. Please check the mailbox.');
}

$paymentInfo = $_SESSION['paymentInfo'];

$firstname = $paymentInfo['firstname'];
$lastname = $paymentInfo['lastname'];
$email = $paymentInfo['email'];
$quantity = $paymentInfo['quantity'];
$amount = $paymentInfo['amount'];
$customId = $paymentInfo['customId'];
$createdTime = date('Y/m/d H:i:s', strtotime($paymentInfo['createdTime']) + 60 * 60 * 9);
$orderId = $paymentInfo['orderId'];

$qrData = urlencode('https://' . $sub . '.sorabi.jp/order.php?id=' . $customId);

$message = <<<EOM
<html><body><p>
$lastname $firstname 様<br>
<br>
この度はお買い上げいただきありがとうございます。<br>
明細と引換証をお送りいたします。<br>
本メールは大切に保管いただき、店舗にご持参いただきますようお願いします。<br>
<br>
【注文番号】 <a href="https://$sub.sorabi.jp/order.php?id=$customId">$customId</a><br>
【PayPal管理番号】 $orderId<br>
<br>
個数: $quantity<br>
金額: $amount 円<br>
決済日時: $createdTime<br>
<br>
ご不明点等ございましたら本メールへのご返信にてご連絡ください。<br>
本メールにお心当たりのない場合はお手数ですが返信にてご一報くださいますようお願い申し上げます。<br>
<br>
皆様のご来場を心よりお待ちしております。<br>
<br>
群馬大学GFL<br>
<a href="https://$sub.sorabi.jp/">https://$sub.sorabi.jp/</a>
</p>
<img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=$qrData&chld=L|1" alt="$customId">
</body></html>
EOM;

$headers = 'Content-Transfer-Encoding: 8bit'
	. PHP_EOL
	. 'From: 群馬大学GFL <sorabi.jp@gmail.com>'
	. PHP_EOL
	.'Content-type: text/html; charset=UTF-8';

$sent = mb_send_mail(
	replaceEnter($email),
	$prefix . '【広瀬川マルシェ】 注文完了メール',
	$message,
	$headers
);

if($sent) {
	$_SESSION['sent'] = true;
}else {
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
			<h2 class="box">注文完了</h2>
			<p class="box"><?=replaceHtml($lastname . ' ' . $firstname)?> 様</p>
			<p class="box">ご注文ありがとうございます。<br>決済が完了しました。</p>
			<p class="box">【注文番号】<br><?=replaceHtml($customId)?></p>
			<p class="box">【PayPal管理番号】<br><?=replaceHtml($orderId)?></p>
			<p class="box">個数: <?=replaceHtml($quantity)?></p>
			<p class="box">金額: <?=replaceHtml($amount)?>円</p>
			<p class="box">決済日時: <?=replaceHtml($createdTime)?></p>
			<p class="box">注文完了メールをお送りしました。引換証を兼ねていますので大切に保管いただき、当日は印刷またはスマホ等の画面にてご提示ください。<br>ご来場をお待ちしております。</p>
            <h3>備考</h3>
            <p class="box">設定の都合上PayPalから送付される領収書の支払先が担当者の個人名となっていますが、決済は正常に完了しています。</p>
		</div>
	</div>
	<?=$footer?>
</body>
</html>