# 多商户输码抽微信红包

initInfo.php 中注释掉了 $redirect_uri = 'http://www.funca.org/redChoco/getOpenIDandResponseFormpage.php';
好像没什么用

## 安全
* `manage/merchantManager`目录的文件可以在本地环境运行，所以不要上传服务器
* `manage/codeManager`目录下的文件因为可以直接没有限制的修改数据，所以必须在需要使用的
   时候再上传至服务器中安全的路径中，以防被人随意访问。并且在使用完成后立刻删除。
* 商户平台的证书必须存放在服务器中不能被从外部直接访问到的目录中


## 注意
* 该项目只包括前端和微信接口部分，不包含后端兑换码处理和数据库的相关内容
* 当前兑换码规则是9位英文字母或数字，如果要变，修改`handleRedPacketDraw.php`中正则表达式


## 微信商户平台的设置、参数和所需文件
1. 开通红包功能：`微信支付商户平台——产品中心——现金红包——开通`
2. 提供API证书：`微信支付商户平台——账户中心——账户设置——API安全——下载证书`
   * PHP开发只需要两个pem后缀的证书
   * 确保证书有效期覆盖活动时间
3. 充值：`微信支付商户平台——交易中心——资金管理——充值`
4. 提供微信公众平台的Appid和AppSecret：`微信公众平台——开发——基本设置`
5. 提供微信支付商户平台商户号：`微信支付商户平台——账户中心——商户设置——基本账户信息——微信支付商户号`
6. 提供商户key（在如下路径设置自定义key）：`微信支付商户平台——账户中心——账户设置——API安全——密钥设置
`
6. 商户名称、红包祝福语、活动名称、红包备注信息。（这些信息将显示在发出的红包中）
7. 设置红包参数：`微信支付商户平台——产品中心——现金红包——产品设置`
    * 设定红包额度范围
    * 调用IP地址：填写程序所在的IP地址
    * 用户领取上限：根据需要设定
    * 防刷等级：根据需要设定


## 拥有微信网页回调域名情况下的使用方法（自己获取用户OpenID）
1. 将`cert.pem`和`key.pem`结尾的两个证书放到`manage/merchantManager/userGenerator/`
   目录中
2. 运行`manage/merchantManager/userGenerator`中的`generator.php`，填写所有表单后，
   点击“生成（使用了自己的回调域名）”按钮，生成配置文件、证书重命名
3. 将证书上传至服务器安全的路径
4. 在`WechatRedPack/`内的`RedPacket.class.php`中引用两个证书的`url`
5. 把配置文件放到`WechatRedPack/merchantsInfo/`，该配置文件中有一行注释，是微信授权
   页的`url`，作为用户访问入口
6. 在`handleRedPacketDraw.php`中，将兑换码发送到后端指定文件
7. 根据后端返回状态码后，向用户提示错误或发送红包
8. 如果调用微信接口发送红包失败，还需要通知后端，将该兑换码重新改为未兑换之前的状态

**本例引入了`check_code.php`并使用了`WXredPacket`类，来向后端发送兑换码，之后如果**
**发送红包失败，使用该类通知后端重置兑换码状态。`check_code.php`由后端开发，不包含**
**在这里**


## 没有微信网页回调域名情况下的使用方法（需要通过拥有域名的第三方转发OpenID）


## 逻辑
1. 如果拥有商户微信公众号的网页回调域名，则可以从我们自己的域名下调用商户微信接口获取其
   用户的OpenID。`manage/merchantManager/userGenerator/generator.php`生成的配置文
   件中的注释内容即为该商户的授权页面。用户访问该链接，经过自动授权后，携带`uniappname`
   参数跳转到`getOpenIDandResponseFormpage.php`。`uniappname`参数值为唯一的商户名，
   用于读取商户的抽奖配置文件、奖品数据和微信公众号及微信支付配置。
2. 如果商户微信公众号的网页回调域名被其他人占用，则必须让占用着代为获取OpenID，然后转发
   到`getOpenIDandResponseFormpage.php`。这时接收到的参数也必须包括`uniappname`，同
   时还包括一个值为用户`OpenID`的参数。
3. `getOpenIDandResponseFormpage.php`中引入`WechatRedPack/getOpenID.php`文件。该
   文件先检查是否有保存`OpenID`的参数。如果有则说明是第三方已经获取好的，这里直接取得该
   `OpenID`。如果没有，说明是直接从授权页面跳转到这里，应该包含用来获取`OpenID`的
   `code`参数，`getOpenID.php`使用该参数获取用户的`OpenID`。如果连`code`参数也没有，
   则说明用户是直接访问的`getOpenIDandResponseFormpage.php`，这是就提示他从商户微信
   公众号的菜单点击进入。
4. 获取用户`OpenID`后，进入前端输码页面，提交后，用户输的码、`OpenID`和`uniappname`一
   并提交给`handleRedPacketDraw.php`
5. `handleRedPacketDraw.php`首先引入`initInfo.php`文件，该文件根据`uniappname`从
   `WechatRedPack/merchantsInfo/`中加载该商户的配置文件。

## 微信接口
* [现金红包](https://pay.weixin.qq.com/wiki/doc/api/tools/cash_coupon.php?chapter=13_1)
