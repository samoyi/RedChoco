<?php 

/*
 * 将该文件放在占用授权回调域名的主机里并得到该文件的访问地址
 * 将用户授权的回调url设置为该地址
 * 用户授权后进入该文件
 * 该文件得到用户的OpenID然后发送给我
 */ 
 
	define('APPID', ''); // 公众平台APPID
	define('APPSECRET', ''); // APPSECRET
	define('RECEIVE_URL', ''); // 我的域名下接受OpenID的地址
	
	// 根据授权的code获得OpenID	
	function getOpenID($sCode)
	{
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' .APPID . '&secret=' . APPSECRET . '&code=' . $sCode . '&grant_type=authorization_code';
		$result = httpGet($url);
		return json_decode($result)->openid;
	}
	
	//发送GET请求
	function httpGet($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	$sCode = $_GET['code']; // 获得从授权页面得到的code
	$sOpenID = getOpenID($sCode); // 根据code获取OpenID
	
	$receiveUrlWithOpenID = RECEIVE_URL . '?openid=' . $sOpenID;
	httpGet($receiveUrlWithOpenID);
	
?>
