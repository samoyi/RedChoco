#输码抽微信红包

## 注意：
* 该项目只包括前端和微信接口部分，不包含后端兑换码数据库相关内容
* 当前兑换码规则是9位英文字母或数字，如果要变，修改```handleRedPacketDraw.php```中正则表达式

## 拥有微信网页回调域名情况下的使用方法（自己获取用户OpenID）
1. 下载证书：PHP只需要以```cert.pem```和```key.pem```，下载后放到 ```manage/userGenerator/``` 中
2. 运行 ```manage/userGenerator```中的```generator.php```，填写所有表单后，点击“生成（使用了自己的回调域名）”按钮，生成配置文件、证书重命名
3. 把证书放到安全的地方
4. 把配置文件放到 ```WechatRedPack/merchantsInfo/```，该配置文件中有一行注释，是微信授权页的url，作为用户访问入口
5. 在```handleRedPacketDraw.php```中，引入你自己的后端兑换码检验代码。本例引入了```check_code.php```并使用了```WXredPacket```类
6. 根据你自己的情况，修改不同结果时的提示文字
7. 确保账户钱够

## 没有微信网页回调域名情况下的使用方法（需要通过拥有域名的第三方转发OpenID）

## 微信接口
[现金红包](https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_1)