<?php

require 'initInfo.php';

echo 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . APPID . '&redirect_uri=' . urlencode($redirect_uri) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect ';
?>
