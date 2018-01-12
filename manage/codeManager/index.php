<pre><?php
ini_set('display_errors', true);
error_reporting(E_ALL);

// 数据量比较大时
set_time_limit(300);
ini_set('memory_limit', '256M');


// 设定以下三个常量并运行文件
define('PREFIX', 'test'); // code前缀最多4位最少一位
define('AMOUNT', 5); // code数量最少一个
define('MERCHANT_ID', 'test'); // 商户ID，用于code的归属，最多16位
define('WECHAT_ID', 'test'); // 该商户的红包是使用哪个公众号发放的



require 'manager.class.php';
$generator = new Generator();


// 新加codes
$result = $generator->addCodes(AMOUNT);
file_put_contents(PREFIX . '_codes.json', json_encode($result['newCodes']));
$generator->closeDBC();
var_dump(count($result['newCodes']));


?>
</pre>
