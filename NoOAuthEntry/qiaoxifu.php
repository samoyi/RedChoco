<?php


$sFetchOpenIDUrl_half = 'http://wxw.bak365.net/qiaoxifu/MobileHtml/PrePurCust/GetOpenID.aspx?Url=';

$sReceiveOpenIDUrl = 'http://www.funca.org/redChoco/getOpenIDandResponseFormpage.php?uniappname=qiaoxifu&openid=';


$sFetchOpenIDUrl = $sFetchOpenIDUrl_half . urlencode($sReceiveOpenIDUrl); 
header("location: $sFetchOpenIDUrl");

?>

   