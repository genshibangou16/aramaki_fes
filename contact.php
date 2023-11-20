<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/elements.php');

genCSP();
sessionCheck();

$hidden1 = null;
$hidden2 = null;
$hidden3 = null;
$email = null;
$name = null;
$body = null;
$title = null;

if(!empty($_POST)) {
	$hidden1 = 'hidden';
	csrfCheck();
	if(!isset($_SESSION['step'])) {
		error('Illegal access', 'Lack of proper authorization');
	}
	if($_SESSION['step']) {
		$hidden2 = 'hidden';
		$_SESSION['step'] = false;
		$message = '件名: ' . $_SESSION['title'] . PHP_EOL . '本文: ' . $_SESSION['body'];
		$headers = 'Content-Transfer-Encoding: 8bit'
			. PHP_EOL
			. 'From: sorabi.jp@gmail.com'
			. PHP_EOL
			. replaceEnter('Reply-To: ' . $_SESSION['name'] . ' <' . $_SESSION['email'] . '>')
			. PHP_EOL
			. 'Content-Type: text/plain; charset=utf-8';
		$sent = mb_send_mail(
			'sorabi.jp@gmail.com',
			'【広瀬川マルシェ】 お問い合わせ',
			$message,
			$headers
		);
		if(!$sent) {
			error('Failed to send', 'エラーが発生しました。再度お問い合わせください。');
		}
	}else {
		$hidden3 = 'hidden';
		$email = $_POST['email'];
		if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$name = $_POST['name'];
			$title = $_POST['title'];
			$body = $_POST['body'];
			$_SESSION['email'] = $email;
			$_SESSION['name'] = $name;
			$_SESSION['title'] = $title;
			$_SESSION['body'] = $body;
			$_SESSION['step'] = true;
		}else {
			error('Incorrect input', 'The format of the email address is wrong.');
		}
	}
}else {
	$hidden2 = 'hidden';
	$hidden3 = 'hidden';
	$_SESSION['step'] = false;
}

$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

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
	<link rel="stylesheet" href="/style/contact.css">
</head>

<body>
	<div id="wrapper">
		<form action="" method="post" class="box <?=$hidden1?>">
			<h2 class="box">お問い合わせ</h2>
			<input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
			<p class="input">氏名</p>
			<input class="input" type="text" name="name" placeholder="群馬太郎" required>
			<p class="input">メールアドレス</p>
			<input class="input" type="email" name="email" placeholder="mail@example.com" required>
			<p class="input">件名</p>
			<input class="input" type="text" name="title" required>
			<p class="input">本文</p>
			<textarea name="body" class="input" rows="4" required></textarea>
			<input class="submit" type="submit">
		</form>
		<form action="" method="post" class="box <?=$hidden2?>">
			<h2 class="box">確認</h2>
			<h3>メールアドレス</h3>
			<p><?=replaceHtml($email)?></p>
			<h3>名前</h3>
			<p><?=replaceHtml($name)?></p>
			<h3>件名</h3>
			<p><?=replaceHtml($title)?></p>
			<h3>本文</h3>
			<p><?=replaceHtml($body)?></p>
			<input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
			<input class="submit" type="submit">
		</form>
		<div class="box <?=$hidden3?>">
			<h2 class="box">完了</h2>
			<p class="box">お問い合わせを受け付けました。<br>数日以内に返信いたします。</p>
		</div>
	</div>
	<?=$footer?>
</body>
</html>