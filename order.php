<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/mysql.php');
require_once('unpublic/elements.php');

genCSP();
sessionCheck();

$hidden = 'hidden';
$login = false;
if(isset($_SESSION['login'])) {
    if($_SESSION['login']) {
        $hidden = '';
        $login = true;
    }
}

if(!postCheck(true) && $login) {
    // token => order token, id => mysql key
    csrfCheck();
    if(isset($_SESSION['ready']) && isset($_SESSION['id']) && isset($_SESSION['token'])) {
        if($_SESSION['ready'] && $_SESSION['id']) {
            $res = pdoDo(
                'update main set status = 1, handed = from_unixtime(?) where id = ?',
                [time() + 32400, $_SESSION['id']],
                true
            );
            if($res[0]) {
                error('Database failue', $res[1]);
            }else {
                transit('order.php?id=' . $_SESSION['token']);
            }
        }else {
            error('Incorrect session', 'Please reload.<br>is null');
        }
    }else {
        error('Incorrect session', 'Please reload.<br>not set');
    }
}else {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $_SESSION['token'] = $id;
    }else {
        error('Wrong access', 'Lack of paramator.<br>Please copy and paste from the mail.');
    }
}

$data = pdoDo(
	'select id, quantity, payment, status, handed from main where token = ?',
	[$id],
	true,
	true
);

if($data[0]) {
	if($data[1]) {
		error('Database failue', $data[1]);
	}else {
		error('Incorrect URL', 'QRコードを正しく読み取れませんでした。');
	}
}else {
	$quantity = $data[1]['quantity'];
	$payment = $data[1]['payment'];
	$status = $data[1]['status'];
	$handed = $data[1]['handed'];
	$_SESSION['id'] = $data[1]['id'];
}

$ready = true;

if($payment != 'COMPLETED' && $payment != 'APPROVED') {
	$ready = false;
}

if($status) {
    $hidden = 'hidden';
	$ready = false;
	$status = '済';
}else {
	$status = '未';
}

$id = replaceHtml($id);

$_SESSION['ready'] = $ready;

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
</head>

<body>
	<div id="wrapper">
		<div class="box">
			<h2 class="box">注文詳細</h2>
			<p>【注文番号】<br><?=$id?></p>
			<p>決済状況: <?=$payment?></p>
			<p>引き換え: <?=$status?></p>
			<p>個数: <?=$quantity?></p>
			<p>引き渡し日時: <?=$handed?></p>
		</div>
        <form action="" method="post" class="box <?=$hidden?>">
            <input type="hidden" value="<?=$csrfToken?>" name="csrfToken">
            <button type="submit" id="submit">引き渡す</button>
        </form>
	</div>
	<?=$footer?>
</body>
</html>