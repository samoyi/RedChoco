<?php


	if( !empty($_GET['openid']) )
	{
		$sOpenID = $_GET['openid'];
	}
	elseif( !empty($_GET['code']) )
	{
		require 'initInfo.php';

		function getOpenID($sCode)
		{
			$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' .APPID . '&secret=' . APPSECRET . '&code=' . $sCode . '&grant_type=authorization_code';
			$result = httpGet($url);
			return json_decode($result)->openid;
		}
		function httpGet($url)//发送GET请求
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			return $output;
		}
		$sCode = $_GET['code'];
		$sOpenID = getOpenID($sCode);
	}
	else
	{
		echo '<h1>请从微信公众号菜单进入领红包活动页面</h1>';
        exit;
	}
?>