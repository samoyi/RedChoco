<?php

define("UniAppName", $_POST["uniappname"]);
require 'initInfo.php';

$sOpenID = $_POST['OpenID'];
$sRedPacketCode = $_POST['ResPacketCode'];
function isResPacketCode($str)
{
	$str = trim($str);
	$re = "/^[A-Za-z][A-Za-z0-9]{8}$/";
	return preg_match($re, $str);
}

if( isResPacketCode($sRedPacketCode) )
{
	$nRedPacketErrorCode = 0;
	$sRedPacketCode = trim($sRedPacketCode);
	
	// 以下三行引入后端兑换码检测文件。该文件不包含在本项目中
	require "check_code.php";
	$WXredPacket  = new WXredPacket($sRedPacketCode, $sOpenID, UniAppName);
	$nCodeStatus = $WXredPacket->redPacket();
	
	switch($nCodeStatus)
	{
		case 2:
		{
			echo "很遗憾，没有中奖哦！";
			break;
		}
		case 3:
		{
			echo "红包码已使用，<br />请重新购买以获得有效的字符。";
			break;
		}
		case 4:
		{
			echo "输入错误，请输入正确的字符。";
			break;
		}
		case 5:
		{
			echo "兑换失败。<br />兑换码输入错误次数过多";
			break;
		}
		default:
		{	
			if( gettype($nCodeStatus) === "integer" )
			{	
				require "WechatRedPack/RedPacket.class.php";
                $RedPacket = new RedPacket;
                $result = $RedPacket->sendOrdinaryRedPacket($sOpenID, $nCodeStatus);

				if($result === 'success'){
					echo "领取成功，请返回拆红包。";
				}
				elseif( strstr($result, '该用户今日操作次数超过限制') ){
					echo "今日领取红包达到最大数量<br />请明日再试";
					$WXredPacket->resetCodeStatus(); // 将该兑换码重新变成没用过的状态
					$WXredPacket->addLogs( $result ); // 记录错误
				}
				elseif( strstr($result, '此请求可能存在风险') ){
					echo "微信账号异常<br />请使用常用的活跃微信号领取";
					$WXredPacket->resetCodeStatus(); // 将该兑换码重新变成没用过的状态
					$WXredPacket->addLogs( $result ); // 记录错误
				}
				else{
					echo "兑换失败。勿刷新页面，<br />请退出后重新进入重试。"; // cUrl错误或其他微信接口相关的失败
					/*
					 * 这里其实用户已经中奖了，但因为发送失败所以没有领到红包
					 * 所以这里重置状态码时应重置为必中的状态，即原是n的要重置为a
					 * resetCodeStatus 函数传递了第一个参数，来实现这一特殊的重置
					 * 同时第二个参数传递中奖金额，用以重置数据库余额
					 */
					$WXredPacket->resetCodeStatus("a", $nCodeStatus); // 将该兑换码重新变成没用过的状态
					$WXredPacket->addLogs( $result ); // 记录错误
				}
			}
			else
			{
				echo  "查询兑换码失败。<br />请稍后重试。"; // 查询数据库时异常返回导致的失败
				$WXredPacket->resetCodeStatus(); // 将该兑换码重新变成没用过的状态
				$WXredPacket->addLogs( '查询数据库时异常返回 ' . $nCodeStatus ); // 记录错误
			}
		}
	}
}
else
{
	echo '兑奖码输入错误';
}

?>