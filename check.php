<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/paypal/paypal.php');
require_once('unpublic/mysql.php');

header('Content-Type: application/json');

if(sessionCheck(1) || postCheck(1) || csrfCheck(1)) {
	echo json_encode(array('statusCode' => 1));
	exit();
}

$amountPosted = $_POST['amount'];
$orderId = $_POST['id'];
$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

$response = check($orderId);
if(!$response[0]) {
	$status = $response[1]->status;
	if($status == 'COMPLETED' || $status == 'APPROVED') {
		$statusCode = 0;
	}else {
		echo json_encode(array(
			'statusCode' => 1,
			'status' => 'Payment status error',
			'description' => 'Payment status is not completed nor approved.'
		));
		exit();
	}
	
	$id = $_SESSION['id'];
	$amount = $response[1]->purchase_units[0]->amount->value;
	$customId = $response[1]->purchase_units[0]->custom_id;
	$lastname = $response[1]->payer->name->surname;
	$firstname = $response[1]->payer->name->given_name;
	
	$res = pdoDo(
		'select token, payment, email from main where id = ?;',
		[$id],
		true
	);
	
	if($res[0]) {
		echo json_encode(array(
			'statusCode' => 1,
			'status' => 'Database failue',
			'description' => 'During select: ' . $res[1]
		));
		exit();
	}else {
		$token = $res[1]['token'];
		$payment = $res[1]['payment'];
		$email = $res[1]['email'];
	}
	
	if($payment) {
		$statusCode = 1;
		$status = 'Double payment';
		$description = 'Paypal payment failure. Please contact to administrator.';
		mb_send_mail(
			'sorabi.jp@gmail.com',
			'決済不良（重複決済）',
			'Order id: ' . $orderId . PHP_EOL . 'Status: ' . $payment,
			'From: sorabi.jp@gmail.com' . PHP_EOL . 'Reply-To: ' . replaceEnter($email)
		);
	}elseif($token != $customId) {
		$statusCode = 1;
		$status = 'Token mismatch';
		$description = 'Paypal payment failure. Please contact to administrator.';
		mb_send_mail(
			'sorabi.jp@gmail.com',
			'決済不良（トークン不一致）',
			'Order id: ' . $orderId . PHP_EOL . 'Server: ' . $token . PHP_EOL . 'Paypal: ' . $customId,
			'From: sorabi.jp@gmail.com' . PHP_EOL . 'Reply-To: ' . replaceEnter($email)
		);
	}elseif($amount != $amountPosted) {
		$statusCode = 1;
		$status = 'Amount mismatch';
		$description = 'Paypal payment failure. Please contact to administrator.';
		mb_send_mail(
			'sorabi.jp@gmail.com',
			'決済不良（金額不一致）',
			'Order id: ' . $orderId . PHP_EOL . 'Posted: ' . $amountPosted . PHP_EOL . 'Paid: ' . $amount,
			'From: sorabi.jp@gmail.com' . PHP_EOL . 'Reply-To: ' . replaceEnter($email)
		);
	}elseif($amount % 190 != 0) {
		$statusCode = 1;
		$status = 'Wrong amount';
		$description = 'Paypal payment failure. Please contact to administrator.';
		mb_send_mail(
			'sorabi.jp@gmail.com',
			'決済不良（金額不正）',
			'Order id: ' . $orderId . PHP_EOL . 'Paid: ' . $amount,
			'From: sorabi.jp@gmail.com' . PHP_EOL . 'Reply-To: ' . replaceEnter($email)
		);
	}else {
		$quantity = $amount / 190;
		$description = array(
			'amount' => $amount,
			'quantity' => $quantity,
			'email' => $email,
			'createdTime' => $response[1]->create_time,
			'firstname' => $firstname,
			'lastname' => $lastname,
			'customId' => $customId,
			'csrfToken' => $csrfToken,
			'orderId' => $orderId
		);
		$res = pdoDo(
			'update main set quantity = ?, orderId = ?, payment = ?,
			firstname = ?, lastname = ? where id = ?;',
			[$quantity, $orderId, $status, $firstname, $lastname, $id],
			true
		);
		if($res[0]) {
			echo json_encode(array(
				'statusCode' => 1,
				'status' => 'Database failue',
				'description' => 'During update: ' . $res[1]
			));
			exit();
		}
	}
}else {
	$statusCode = 1;
	$status = $response[1]->issue;
	$description = $response[1]->description;
}

$param = array(
	'statusCode' => $statusCode,
	'status' => $status,
	'csrfToken' => $csrfToken,
	'description' => $description
);

if(!$statusCode) {
	$_SESSION['paymentInfo'] = $description;
}

$param = json_encode($param);

echo $param;