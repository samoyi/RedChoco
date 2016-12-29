<?php
	if( !empty($_GET['sPHPContent']) )
	{
		$sPHPContent = $_GET['sPHPContent'];
		$sRedirectUrl = $_GET['sRedirectUrl'];
		$sAppID = $_GET['sAppID'];
		$sUniAppName = $_GET['sUniAppName'];
		// TODO 为什么JS中回调域名编码后传过来会自动解码？
		$sOAuthUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $sAppID . "&redirect_uri=" . urlencode($sRedirectUrl . '?uniappname=' . $sUniAppName) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
		
		$sContent = '<?php' . PHP_EOL . PHP_EOL . $sPHPContent . '// ' . $sOAuthUrl . PHP_EOL . PHP_EOL . '?>';
		file_put_contents($sUniAppName.'_initInfo.php', $sContent);
		exit;
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>generator</title>
<style>
#initPHPInfo input, #uniappname
{
	width: 18em;
}
#redirectUrl
{
	width: 36em;
}
</style>
</head>
<body>
<h1>请在支持ES6的浏览器中运行</h1>
<section id="initPHPInfo">
	<input type="text" id="appid" placeholder="APPID" /><br />
	<input type="text" id="appsecret" placeholder="APPSECRET" /><br />
	<input type="text" id="mch_key" placeholder="MCH_KEY" /><br />
	<input type="text" id="send_name" placeholder="SEND_NAME" /><br />
	<input type="number" id="mch_id" placeholder="MCH_ID" /><br />
	<input type="text" id="wishing" placeholder="WISHING" /><br />
	<input type="text" id="act_name" placeholder="ACT_NAME" /><br />
	<input type="text" id="remark" placeholder="REMARK" />
</section>
<input type="text" id="uniappname" placeholder="uniappname" /><br />
<input type="text" id="redirectUrl" value="http://www.funca.org/redChoco/getOpenIDandResponseFormpage.php" placeholder="授权回调" /><br /><br />
<input type="button" id="generate" value="生成" />

</body>
<script>
"use strict";

let sInitPHPContent = "",
	aInitPHPInfo = Array.from( document.querySelector("#initPHPInfo").querySelectorAll("input") ),
	oGenerateBtn = document.querySelector("#generate");

oGenerateBtn.addEventListener("click", function()
{	
	let sUniAppName = document.querySelector("#uniappname").value.trim(),
		sRedirectUrl = document.querySelector("#redirectUrl").value.trim(),
		sAppID =  document.querySelector("#appid").value.trim();
		
	if( isNoVacancyInput( aInitPHPInfo ) && sUniAppName && sRedirectUrl )
	{
		let sPHPContent = generatePHPContent( aInitPHPInfo, sUniAppName);
		let xhr = new XMLHttpRequest();
		xhr.open("get", "generator.php?sPHPContent=" + encodeURIComponent(sPHPContent) + "&sUniAppName=" + sUniAppName + "&sRedirectUrl=" + encodeURIComponent(sRedirectUrl) + "&sAppID=" + sAppID, true);
		xhr.send(null);
	}
	else
	{
		alert("没填完");
	}
});	


function isNoVacancyInput( aInput )
{
	aInput = Array.from(aInput);
	let bVacancy = aInput.some(function(item)
	{	
		return item.value.trim() === "";
	});
	return !bVacancy;
} 

// 不包括授权url
function generatePHPContent( aInput, sUniAppName )
{
	let sInitPHPContent = "";
	aInput.forEach(function(item)
	{
		sInitPHPContent += "define('" + item.id.toUpperCase() + "', '" + item.value.trim() + "');\n";
	});
	sInitPHPContent += "\n";
	sInitPHPContent += "define('CERT_PEM_NAME', '" + sUniAppName + "_cert.pem');\n";
	sInitPHPContent += "define('KEY_PEM_NAME', '" + sUniAppName + "_key.pem');\n\n";
	return sInitPHPContent;
}	

function generateOAuthUrl(sRedirectUrl, sAppID)
{
	return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" + sAppID + "&redirect_uri=" + encodeURIComponent(sRedirectUrl) + '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
}

</script>





</html>