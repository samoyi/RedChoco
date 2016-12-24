<?php
    define("UniAppName", $_GET["uniappname"]); // 用来区别是从哪个公众号来的
    require "getOpenID.php";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<link href="css/main.css" type="text/css" rel="stylesheet" />
<title>红巧要分享 红包要疯抢</title>
</head>
<body>
<section id="container">
	<div id="statement">
		<p>* 此次活动的最终解释权归官方所有</p>
	</div>
    <div id="mat">
    </div>
    <div id="redpacket"></div>
    <div id="form">
        <input id="text" type="text" placeholder="输入9位红包码" name="ResPacketCode" pattern="^[a-e][a-z0-9]{8}$" title="红包码输入错误" />
        <div id="submit"></div>
    </div>
	<div id="resultMat">
		<p></p>
	</div>
	
</section>
</body>
<script>
var sOpenID = <?php echo json_encode($sOpenID); ?>,
    sUniappname = <?php echo json_encode(UniAppName); ?>;
</script>
<script src="js/main.js"></script>
</html>