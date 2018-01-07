<?php
// 手动生成授权页面url
define('APPID', '');
$redirect_uri = '';

echo 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . APPID . '&redirect_uri=' . urlencode($redirect_uri) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect ';

?>
