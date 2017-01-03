<?php

	if( !empty($_GET['sPHPContent']) )
	{
		// 生成配置文件————————————————————————————————————————————————
		$sPHPContent = $_GET['sPHPContent'];
		$sRedirectUrl = $_GET['sRedirectUrl'];
		$sAppID = $_GET['sAppID'];
		$sUniAppName = $_GET['sUniAppName'];
		
		
		if( empty($_GET['sAppSecret']) ) // 使用了自己的回调域名
		{
			// 授权页url
			$sOAuthUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $sAppID . "&redirect_uri=" . urlencode($sRedirectUrl . '?uniappname=' . $sUniAppName) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
			// 完整的配置文件字符内容
			$sContent = '<?php' . PHP_EOL . PHP_EOL . $sPHPContent . '// ' . $sOAuthUrl . PHP_EOL . PHP_EOL . '?>';
		}
		else// 使用了第三方的回调域名
		{
			$sContent = '<?php' . PHP_EOL . PHP_EOL . $sPHPContent . '?>';
			// 生成放在第三方域名里的文件
			$contentOfSendOpenIDToMe = file_get_contents("generatorComponets/sendOpenIDToMe.php");
			//str_replace("''", "'".$sAppID."'", $contentOfSendOpenIDToMe);
			$index = 0;
			$nPos = 0;
			$offset = 0;
			do{
				$nPos = strpos($contentOfSendOpenIDToMe, "''", $offset);
				$offset = ++$nPos;
				file_put_contents("words.txt", $nPos.' ', FILE_APPEND);
				$index++;
			}
			while($index<5);
			
/* 			str_replace("''", "'".$sAppID."'", $contentOfSendOpenIDToMe, $index);
			file_put_contents("index0.txt", $index);
 			str_replace("''", "'".$_GET['sAppSecret']."'", $contentOfSendOpenIDToMe, $index);
			file_put_contents("index1.txt", $index);
			str_replace("''", "'".$sRedirectUrl."'", $contentOfSendOpenIDToMe, $index);
			file_put_contents("index2.txt", $index);
			str_replace("''", "'".$sUniAppName."'", $contentOfSendOpenIDToMe, $index); 
file_put_contents("index3.txt", $index);		
			file_put_contents('sendOpenIDToMe.php', $contentOfSendOpenIDToMe); */
		}
		
		// 生成配置文件
		file_put_contents($sUniAppName.'_initInfo.php', $sContent);

		
		
		// 检查两个证书文件放了几个，放了的直接用uniAppName重命名
		function checkPemExistanceStateAndRenameExsited($sUniAppName)
		{
			$bIsCertPemExist = false;
			$bIsCertKeyExist = false;
			$aCwdFiles = scandir( getcwd() );
			foreach( $aCwdFiles as $fileName )
			{
				if( strstr($fileName, 'cert.pem') ){
					$bIsCertPemExist = true;
					rename($fileName, $sUniAppName.'_cert.pem');
				}
				if( strstr($fileName, 'key.pem') ){
					$bIsCertKeyExist = true;
					rename($fileName, $sUniAppName.'_key.pem');
				}
			}
			if( $bIsCertPemExist && $bIsCertKeyExist ){
				return "2";
			}
			elseif( $bIsCertPemExist && !$bIsCertKeyExist ){
				return 'key';
			}
			elseif( !$bIsCertPemExist && $bIsCertKeyExist ){
				return 'cert';
			}
			else{
				return "0";
			}
		}
		
		
		switch( checkPemExistanceStateAndRenameExsited($sUniAppName) )
		{
			case "2":{
				echo "配置文件已生成\n两个证书都改好名了";
				break;
			}
			case 'cert':{
				echo "配置文件已生成\n缺少cert.pem证书，key.pem证书改好名了";
				break;
			}
			case 'key':{
				echo "配置文件已生成\n缺少key.pem证书，cert.pem证书改好名了";
				break;
			}
			case "0":{
				echo "配置文件已生成\n缺少pem证书文件";
				break;
			}
		}
		exit;
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>userGenerator</title>
<style>
#initPHPInfo input, #uniappname{
	width: 18em;
}
#redirectUrl{
	width: 36em;
}
#es6Tip{
	font-weight: bold;
	font-size:0.8em;
}
</style>
</head>
<body>
<h1>生成配置文件（内含获取授权url），用uniAppName重命名证书文件</h1>
<section>
	<p>把下载好的两个证书文件放在该目录并填写提交以下表单</p>
	<p id="es6Tip">要在支持ES6的浏览器中运行</p>
</section>

<section id="initPHPInfo">
	<input type="text" id="appid" placeholder="公众平台APPID" /><br />
	<input type="text" id="appsecret" placeholder="公众平台APPSECRET" /><br />
	<input type="text" id="mch_key" placeholder="商户平台MCH_KEY" /><br />
	<input type="text" id="send_name" placeholder="红包发送者名称SEND_NAME" /><br />
	<input type="number" id="mch_id" placeholder="商户ID MCH_ID" /><br />
	<input type="text" id="wishing" placeholder="红包祝福语 WISHING" /><br />
	<input type="text" id="act_name" placeholder="红包活动名称 ACT_NAME" /><br />
	<input type="text" id="remark" placeholder="红包备注 REMARK" />
</section>
<input type="text" id="uniappname" placeholder="uniAppName" /><br />
<input type="text" id="redirectUrl" placeholder="授权回调url。正常情况下这个值是固定的，可以直接写在HTML中" /><br /><br />
<input type="button" id="generate_ownDomain" value="生成（使用了自己的回调域名）" />
<input type="button" id="generate_otherDomain" value="生成（使用了第三方的回调域名）" />

</body>
<script>
"use strict";

let sInitPHPContent = "",
	aInitPHPInfo = Array.from( document.querySelector("#initPHPInfo").querySelectorAll("input") ),
	oGenerateBtn_ownDomain = document.querySelector("#generate_ownDomain"),
	generate_otherDomain = document.querySelector("#generate_otherDomain");

function sendInfoToPhp(bIsOtherDomain)
{
	let sUniAppName = document.querySelector("#uniappname").value.trim(),
		sRedirectUrl = document.querySelector("#redirectUrl").value.trim(),
		sAppID =  document.querySelector("#appid").value.trim(),
		sAppSecret =  document.querySelector("#appsecret").value.trim();
		
	if( isNoVacancyInput( aInitPHPInfo ) && sUniAppName && sRedirectUrl )
	{
		let sPHPContent = generatePHPContent( aInitPHPInfo, sUniAppName);
		let xhr = new XMLHttpRequest();
		xhr.addEventListener('readystatechange', function()
		{
			if (xhr.readyState == 4){
				if ((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304){
					alert( xhr.responseText );
				}
			}
		}, false);
		if( bIsOtherDomain ){
			xhr.open("get", <?php echo json_encode($_SERVER['PHP_SELF']); ?> + "?sPHPContent=" + encodeURIComponent(sPHPContent) + "&sUniAppName=" + sUniAppName + "&sRedirectUrl=" + encodeURIComponent(sRedirectUrl) + "&sAppID=" + sAppID + "&sAppSecret=" + sAppSecret, true);
		}
		else{
			xhr.open("get", <?php echo json_encode($_SERVER['PHP_SELF']); ?> + "?sPHPContent=" + encodeURIComponent(sPHPContent) + "&sUniAppName=" + sUniAppName + "&sRedirectUrl=" + encodeURIComponent(sRedirectUrl) + "&sAppID=" + sAppID, true);
		}
		
		xhr.send(null);
	}
	else{
		alert("没填完");
	}
}
oGenerateBtn_ownDomain.addEventListener("click", function()
{	
	generate_otherDomain.disabled = 'disabled';
	sendInfoToPhp();
});	
generate_otherDomain.addEventListener("click", function()
{	
	oGenerateBtn_ownDomain.disabled = 'disabled';
	sendInfoToPhp(true);
});	

// 检查参数数组中每一个input节点是否都填了有效字符
function isNoVacancyInput( aInput )
{
	aInput = Array.from(aInput);
	let bVacancy = aInput.some(function(item)
	{	
		return item.value.trim() === "";
	});
	return !bVacancy;
} 

// 生成配置文件的内容字符
/*
 * 不包括授权url部分和头尾的php标签
 */
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

</script>
</html>