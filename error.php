<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/elements.php');

sessionCheck();
genCSP();

if(isset($_SESSION['error'])) {
	$error = $_SESSION['error'];
	if(isset($_SESSION['errorMessage'])) {
		$errorMessage = $_SESSION['errorMessage'];
	}else {
		$errorMessage = '';
	}
}else {
	transit('index.php');
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
			<h2 class="box">Error: <?=$error?></h2>
			<p class="box"><?=$errorMessage?></p>
	<a href="https://<?=$sub?>.sorabi.jp/index.php">トップページへ戻る</a>
		</div>
	</div>
	<?=$footer?>
</body>
</html>