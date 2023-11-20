<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/mysql.php');
require_once('unpublic/elements.php');
require_once('unpublic/paypal/paypal.php');

$nonce1 = genToken();
$nonce2 = genToken();
$nonce3 = genToken();
$nonce4 = genToken();

genCSP("style-src 'unsafe-inline' 'self'; script-src 'strict-dynamic' 'nonce-" . $nonce1 . "' 'nonce-" . $nonce2 . "' 'nonce-" . $nonce3 . "' 'nonce-" . $nonce4 . "' 'self'; img-src data: *.paypal.com 'self'; frame-src *.paypal.com 'self'; connect-src *.paypal.com 'self'");

sessionCheck();
getCheck();

if(isset($_GET['token'])) {
	$tokenGet = replaceHtml($_GET['token']);
}else {
	error('Illegal Access', '入力されたURLが誤っています。<br>メール内のURLを全てコピーしてブラウザに貼り付けてください。');
}

$res = pdoDo(
	'select * from register where token = ?;',
	[$tokenGet],
	true,
	true
);

if($res[0]) {
	if($res[1]) {
		error('Database failue', $res[1]);
	}else {
		error('Invalid URL', '期限切れ、もしくはすでにアクセスされたURLです。<br>再登録してください。');
	}
}else {
	$reg = $res[1];
	pdoDo(
		'delete from register where id = ?',
		[$reg['id']]
	);
    
    $flag = true;
    while($flag) {
        $token = genToken();
        $flag = pdoDo(
            'insert into main (id, email, token) values (?, ?, ?);',
            [$reg['id'], $reg['email'], $token],
            true
        );
        $flag = $flag[0];
    }
	
	$_SESSION['id'] = $reg['id'];
}

$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

$_SESSION['sent'] = false;

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
	<link rel="stylesheet" href="/style/purchase.css">
</head>

<body>
	<div id="wrapper">
		<div class="box hidden" id="messages">
			<h2 class="box">決済情報</h2>
			<p class="box" id="idBox"></p>
			<p class="box" id="messageBox1"></p>
			<p class="box" id="messageBox2"></p>
		</div>
		<div class="box" id="quantity">
			<h2 class="box">購入数</h2>
			<div id="number_wrapper">
				<p id="price">デルニー1個 190円</p>
				<div id="buttons_wrapper">
					<button id="btnDown" type="button">
						<div id="minus"></div>
					</button>
					<button id="btnUp" type="button">
						<div id="plus1"></div>
						<div id="plus2"></div>
					</button>
				</div>
			</div>
			<button type="button" id="btnQuantity">次へ</button>
		</div>
		<form class="box h-adr hidden" id="addressBox">
			<h2 class="box">請求先住所</h2>
			<p class="box">PayPalに送信する請求先住所です。本サイトでは情報を保管しません。</p>
			
			<input type="hidden" id="csrfToken" value="<?=$csrfToken?>">
			<input type="hidden" id="token" value="<?=$token?>">
			<input type="hidden" id="email" value="<?=$reg['email']?>">
			
			<input type="hidden" class="p-country-name" value="Japan">
			<input type="hidden" class="p-region" id="region">
			<input type="hidden" class="p-locality" id="locality">
			<input type="hidden" class="p-street-address" id="street">
			<input type="hidden" class="p-extended-address">
			<input type="text" class="hidden"> <!-- Hack prevents sending with the enter key -->
			<div id="postalWrapper">
				<label for="postalCode">郵便番号</label>
				<input type="text" class="p-postal-code" size="8" maxlength="8" id="postalCode" placeholder="3718510">
			</div>
			<button id="btnPostal" type="button">次へ</button>
		</form>
		<div id="paypal_wrapper" class="hidden">
			<div class="box">
				<h2 class="box">決済</h2>
				<p class="box" id="final"></p>
				<p class="box">購入後のキャンセル・変更はできません。購入数と支払額をよくご確認下さい。<br>デルニーの受け取りには2023/03/19(日)に開催される広瀬川マルシェへのご来場が必要です。<br><a href="policy.php" target="_blank"　rel="noopener noreferrer">利用規約</a>にご同意いただいた上で決済を行ってください。<br>入力された情報は直接PayPalへ送信され本サイトには一切保管されません。</p>
				<div id="paypal-button-container"></div>
				<button id="btnBack" type="button">戻る</button>
			</div>
		</div>
	</div>
	<div id="processing" class="hidden">
		<h1>決済処理中</h1>
	</div>
	<?=$footer?>
	
	<script nonce="<?=$nonce1?>" src="https://www.paypal.com/sdk/js?client-id=<?=$clientId?>&locale=ja_JP&currency=JPY&enable-funding=card,credit&intent=capture"></script>
    <script nonce="<?=$nonce2?>">
        const sub = '<?=$sub?>';
    </script>
	<script nonce="<?=$nonce3?>" src="/script/purchase.js"></script>
	<script nonce="<?=$nonce4?>" src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8" defer></script>
</body>
</html>