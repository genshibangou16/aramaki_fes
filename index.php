<?php

session_start();
require_once('unpublic/func.php');
require_once('unpublic/elements.php');
require_once('unpublic/mysql.php');

sessionCheck();
genCSP("script-src www.google.com www.gstatic.com 'self'; frame-src www.google.com 'self'");

$stop = <<<EOE
<div class="box">
<h2 class="box">販売終了</h2>
<p class="box">予定数の販売が終了しました。誠に勝手ながら受け付けは終了させていただきます。<br>ありがとうございました。</p>
</div>
EOE;

if(countOrder()['available'] < 10) {
    $hidden = 'hidden';
}else {
    $hidden = '';
    $stop = '';
}

$csrfToken = genToken();
$_SESSION['csrfToken'] = $csrfToken;

$_SESSION['sent'] = false;

?>

<!doctype html>
<html lang="ja">
<head prefix="og: http://ogp.me/ns#">
	<meta charset="utf-8">
	<title><?=$prefix?>広瀬川マルシェ | GFL デルニー</title>
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="広瀬川マルシェにてGFL生とウクライナ留学生が協力して出店する、ウクライナ料理「デルニー」屋台のオンライン決済サイトです。">
    <meta property="og:url" content="https://<?=$sub?>.sorabi.jp/">
    <meta property="og:type" content="website">
    <meta property="og:title" content="【広瀬川マルシェ】ウクライナ料理「デルニー」">
    <meta property="og:description" content="広瀬川マルシェにてGFL生とウクライナ留学生が協力して出店する、ウクライナ料理「デルニー」屋台のオンライン決済サイトです。">
    <meta property="og:site_name" content="【広瀬川マルシェ】ウクライナ料理「デルニー」">
    <meta property="og:image" content="https://<?=$sub?>.sorabi.jp/image/og.webp">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@GundaiG">
    <link rel="apple-touch-icon" href="/image/apple-touch-icon.png">
	<link rel="stylesheet" href="/style/general.css">
	<link rel="stylesheet" href="/style/index.css">
    <script src="https://www.google.com/recaptcha/enterprise.js?render=6LffPhwjAAAAAK656k0ij7GlHQLaBY5OUARrgjNF" async defer></script>
</head>

<body>
	<div id="wrapper">
		<div id="fes">
			<h1>広瀬川マルシェ</h1>
			<h2>GFL</h2>
		</div>
		<div id="deruni">
			<p>ウクライナ料理</p>
			<h1>デルニー</h1>
		</div>
		<div id="flag">
			<div id="flag_blue"></div>
			<div id="flag_yellow"></div>
		</div>
		<h2 id="site">オンライン購入サイト</h2>
        <?=$stop?>
		<form action="mail.php" method="post" class="box <?=$hidden?>" id="form1">
			<h2 class="box form_title">購入はこちら</h2>
			<p class="form_description">入力されたメールアドレスへオンライン購入用ページのURLをお送りいたします。<br>PayPal/クレジットカード/デビットカードでの決済に対応しています。<a href="policy.php">利用規約</a>にご同意の上ご利用ください。</p>
			<input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
			<input class="email" type="email" name="email" placeholder="mail@example.com" id="email1">
            <div>
    			<button class="submit g-recaptcha"
                       type="submit"
                       data-sitekey="6LffPhwjAAAAAK656k0ij7GlHQLaBY5OUARrgjNF"
                       data-callback='onSubmit1'
                       data-action='submit'>送信</button>
            </div>
            <p class="recaptcha">このサイトはreCAPTCHAとGoogleによって保護されています。<a href="https://policies.google.com/privacy" class="recaptcha">プライバシーポリシー</a>と<a href="https://policies.google.com/terms" class="recaptcha">利用規約</a>が適用されます。</p>
		</form>
		<div id="photoBox">
			<img id="photo" src="image/deruni.webp?new" alt="デルニーの写真">
		</div>
		<div class="box">
			<h2 class="box">デルニーとは</h2>
			<p class="box">デルニーとはジャガイモで作ったパンケーキです。日本で食べ慣れたものとしてはハッシュドポテトやチヂミが近いかもしれません。原材料はジャガイモ、玉ねぎ、小麦粉、卵、味付けも塩コショウでととてもシンプル。ウクライナではサワークリームをかけて食べますが、屋台では生ものの提供ができないので今回はじゃがいもの甘味をお楽しみ下さい。外はカリッと中はもちっと、世界有数のジャガイモの生産国、ウクライナの味をご賞味ください。</p>
		</div>
		<div class="box">
			<h2 class="box">価格</h2>
			<table class="box">
				<tr>
					<th class="box">オンライン購入</th>
					<td class="box">190円</td>
				</tr>
				<tr>
					<th class="box">現金購入</th>
					<td class="box">200円</td>
				</tr>
			</table>
		</div>
		<div class="box">
			<h2 class="box">広瀬川マルシェについて</h2>
			<p class="box">広瀬川マルシェは前橋市街地の広瀬川河畔で3月19日に開催され、ハンドメード作家約70組が作品の展示即売を行います。</p>
			<table class="box">
				<tr>
					<th class="box">開催日</th>
					<td class="box">3/19(日)</td>
				</tr>
				<tr>
					<th class="box">開催時間</th>
					<td class="box">10:00~16:00</td>
				</tr>
			</table>
			<p class="box">売り切れ次第終了となります。ご了承ください。</p>
		</div>
		<div class="box">
			<h2 class="box">GFLについて</h2>
			<p class="box">GFL（グローバル・フロンティア・リーダー）プログラムは世界の先端分野で活躍できる人材を育成することを目標とした群馬大学の特別プログラムです。所属学生は留学やディスカッション、講演会等を通して、語学力をはじめとしたコミュニケーション能力やSDGsについての意識を高め、幅広い視野を持ち国内外で主体的に貢献できる人材となれるよう活動しています。今回の出店に当たっては食品ロス削減の観点から低農薬・低化学肥料で栽培された傷あり（小さな傷が認められる程度で味、安全性には支障ありません）のジャガイモを使用しています。</p>
		</div>
		<form action="mail.php" method="post" class="box <?=$hidden?>" id="form2">
			<h2 class="box form_title">オンライン購入</h2>
			<p class="form_description">入力されたメールアドレスへオンライン購入用ページのURLをお送りいたします。<br>PayPal/クレジットカード/デビットカードでの決済に対応しています。<a href="policy.php">利用規約</a>にご同意の上ご利用ください。</p>
			<input type="hidden" name="csrfToken" value="<?=$csrfToken?>">
			<input class="email" type="email" name="email" placeholder="mail@example.com" id="email2">
            <div>
    			<button class="submit g-recaptcha"
                       type="submit"
                       data-sitekey="6LffPhwjAAAAAK656k0ij7GlHQLaBY5OUARrgjNF"
                       data-callback='onSubmit2'
                       data-action='submit'>送信</button>
            </div>
            <p class="recaptcha">このサイトはreCAPTCHAとGoogleによって保護されています。<a href="https://policies.google.com/privacy" class="recaptcha">プライバシーポリシー</a>と<a href="https://policies.google.com/terms" class="recaptcha">利用規約</a>が適用されます。</p>
		</form>
	</div>
	<?=$footer?>
    <script src="/script/index.js"></script>
</body>
</html>