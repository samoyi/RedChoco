<?php
	
	function sendRedPacket($sOpenID, $nCent )
	{	
		require "RedPacket.class.php";
		$RedPacket = new RedPacket;
		return $result = $RedPacket->sendOrdinaryRedPacket($sOpenID, $nCent); 
	}
	
?>
