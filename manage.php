<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/elements.php');
require_once('unpublic/mysql.php');

sessionCheck();
genCSP();

loginCheck();
$login = $_SESSION['login'];
$su = $_SESSION['su'];

if($su) {
    $hidden_total = '';
}else {
    $hidden_total = 'hidden';
}

$now = date('Y/m/d H:i:s', strtotime('+9 hours'));

$counts = countOrder();

$info = '';
$hidden = 'hidden';

if(!postCheck(true)) {
    if(isset($_POST['type']) && isset($_POST['amount'])) {
        csrfCheck();
        if(isset($_POST['total'])) {
            $total = $_POST['total'];
            if($total && $su) {
                if(is_numeric($total)) {
                    updateCount(0, 0, $total);
                    $info = '総数操作: ' . $total . '<br>';
                }
            }
        }
        $type = $_POST['type'];
        $amount = $_POST['amount'];
        if(is_numeric($amount)) {
            if($type == 'cash') {
                updateCount($amount, 0);
                $info .= '現金購入: ' . $amount . '個';
            }elseif($type == 'invite') {
                updateCount(0, $amount);
                $info .= '招待状: ' . $amount . '個';
            }else {
                $_POST = array();
                error('Incorrect value', 'Posted value is not correct.<br>type mismatch');
            }
            transit('manage.php');
            $hidden = '';
        }else {
            $_POST = array();
            error('Incorrect value', 'Posted value is not correct.<br>not numeric');
        }
    }else {
        $_POST = array();
        error('Incorrect value', 'Posted value is not correct.<br>not posted both value.');
    }
}

$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

?>

<!doctype html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title><?=$prefix?>広瀬川マルシェ | GFL 管理画面</title>
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="apple-touch-icon" href="/image/apple-touch-icon.png">
	<link rel="stylesheet" href="/style/general.css">
    <link rel="stylesheet" href="/style/manage.css?<?=genToken()?>">
</head>

<body>
	<div id="wrapper">
        <div class="box">
            <h2 id="form_title" class="box">ステータス</h2>
			<table class="box">
				<tr>
					<th class="box">販売可能数</th>
					<td class="box"><?=$counts['available']?>個</td>
				</tr>
				<tr>
					<th class="box">PayPal販売数</th>
					<td class="box"><?=$counts['paypal']?>個</td>
				</tr>
				<tr>
					<th class="box">現金販売数</th>
					<td class="box"><?=$counts['inperson']?>個</td>
				</tr>
				<tr>
					<th class="box">PayPal未納品</th>
					<td class="box"><?=$counts['wait']?>個</td>
				</tr>
				<tr class="hidden">
					<th class="box">販売済未納品</th>
					<td class="box"><?=$counts['wait'] - $counts['invited']?>個</td>
				</tr>
			</table>
        </div>
        <div class="box <?=$hidden?>">
            <h2 class="box"><?=$now?></h2>
            <p id="done"><?=$info?></p>
        </div>
		<form action="" method="post">
            <div class="box" id="quantity">
                <h2 class="box">購入操作</h2>
                <div class="items_wrapper hidden">
                    <input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
                    <div>
                        <input type="radio" name="type" value="cash" id="cash" checked required>
                        <label for="cash" class="radiolabel">現金</label>
                    </div>
                    <div>
                        <input type="radio" name="type" value="invite" id="invited">
                        <label for="invited" class="radiolabel">招待状</label> 
                    </div>
                </div>
			    <div class="items_wrapper">
			    	<button id="btnDown" type="button">
			    		<div id="minus"></div>
			    	</button>
			    	<input id="amount" value="1" name="amount" type="number">
			    	<button id="btnUp" type="button">
			    		<div id="plus1"></div>
			    		<div id="plus2"></div>
			    	</button>
			    </div>
			    <button type="button" id="btnQuantity">次へ</button>
            </div>
            <div class="box hidden" id="final">
                <p class="check" id="check"></p>
                <div class="items_wrapper">
                    <button type="button" id="btnBack">戻る</button>
                    <button type="submit" class="submit">決定</button>
                </div>
            </div>
            <div class="box <?=$hidden_total?>">
                <h2 class="box">総数操作</h2>
                <div class="items_wrapper">
                    <input type="number" name="total" id="total">
                </div>
            </div>
		</form>
	</div>
	<?=$footer?>
    <script src="/script/manage.js?<?=genToken()?>"></script>
</body>
</html>