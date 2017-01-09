<?php

	
	$aOpenIDArgName = array( //第三方提供的OpenID的参数名可能不统一，这里列出所有的参数名
		'openid', 'WchOpenID'
	);
	function hasOpenIDArg($aOpenIDArgName)
	{
		foreach($aOpenIDArgName as $value){
			if( !empty($_GET[$value]) ){
				return true;
			}
		}
		return false;
	}
	
	if( hasOpenIDArg($aOpenIDArgName) ) // 从第三方接受OpenID
	{
		$sOpenID = $_GET['openid'];
	}
	elseif( !empty($_GET['code']) ) // 自己进行授权
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