var oContainer = document.querySelector("#container"),
	oBtn = document.querySelector("#submit"),
    oTxt = document.querySelector("#text"),
    oStatement = document.querySelector("#statement"),
	oRedPacket = document.querySelector("#redpacket"),
    oMat = document.querySelector("#mat"),
    oResultMat = document.querySelector("#resultMat");

oContainer.style.height = oRedPacket.style.height = oMat.style.height = oStatement.style.height = oContainer.offsetHeight + "px";
oContainer.style.width = oRedPacket.style.width = oMat.style.width = oStatement.style.width = oContainer.offsetWidth + "px";


var oResult = oResultMat.querySelector("p");
function showResult()
{
    oResult.innerHTML = "正在检测红包码……";
    oResultMat.style.display = "block";
    oResultMat.style.backgroundColor = "rgba(0,0,0,0.5)";
}
function hideResult()
{
    oResultMat.style.display = "none";
    oResultMat.style.backgroundColor = "transparent";
    oResult.innerHTML = "";
    document.removeEventListener("touchend", hideResult);
}

var bTouched = false;
oBtn.addEventListener("touchend", function()
{
	if( !bTouched )
	{
		bTouched = true;
		showResult();
		var sRedPacketCode = oTxt.value.trim();
		var xhr = new XMLHttpRequest();
		xhr.addEventListener('readystatechange', function(){
			if (xhr.readyState == 4){
				if ((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304){
					oResult.innerHTML = xhr.responseText;
					document.addEventListener("touchend", hideResult);
				}
				else{
					oResult.innerHTML = "发送失败。网络错误，请返回重试。";
				}
				bTouched = false;
			}
		}, false);
		xhr.open("post", "handleRedPacketDraw.php", true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		var data =  "OpenID=" + sOpenID + "&ResPacketCode=" + sRedPacketCode + "&uniappname=" + sUniappname;
		xhr.send(data);
	}
});

document.addEventListener("touchmove",function(ev)
{
    ev.preventDefault();
});
