<?php

if($_SERVER['REQUEST_URI'] == '/index.php' || $_SERVER['REQUEST_URI'] == '/') {
	$linkToIndex = null;
}else {
	$linkToIndex = '<a class="footer_link" href="index.php">トップ</a>';
}

$footer = <<<EOE
<footer>
	<div id="footer_link_box">
		$linkToIndex
		<a class="footer_link" href="policy.php">利用規約</a>
		<a class="footer_link" href="contact.php">お問い合わせ</a>
	</div>
	<div id="footer_sns">
		<a class="footer_sns_link" href="https://www.instagram.com/gundai_gfl/">
			<img class="footer_sns_image" src="/image/Instagram.svg" alt="GFL公式インスタグラム">
		</a>
		<a class="footer_sns_link" href="https://twitter.com/GundaiG">
			<img class="footer_sns_image" src="/image/Twitter.svg" alt="GFL公式ツイッター">
		</a>
	</div>
	<a class="footer_link" href="https://gfl.jimu.gunma-u.ac.jp/">GFL公式サイト</a>
	<small id="footer_text">Copyright &copy; 2022 Gunma univ. GFL</small>
</footer>
EOE;
