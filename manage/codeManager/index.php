<pre><?php
ini_set('display_errors', true);
error_reporting(E_ALL);

// 设定以下三个常量并运行文件
define('PREFIX', 'xxdg'); // code前缀最多4位最少一位
define('AMOUNT', 5500); // code数量最少一个
define('MERCHANT_ID', 'xxdg'); // 商户ID，用于code的归属，最多16位


require 'manager.class.php';
$generator = new Generator(PREFIX, AMOUNT, MERCHANT_ID);



// 新加codes
$result = $generator->addCodes(AMOUNT);
file_put_contents(PREFIX . '_codes.json', json_encode($result['newCodes']));
$generator->closeDBC();
var_dump(count($result['newCodes']));


// 作废codes
// $aInvalidCode = array(
//     "test69r7",
//     "testmzf9",
//     "test6bn7",
//     "testyqu9",
//     "testck9s"
// );
// $result = $generator->invalidate($aInvalidCode);
// $generator->closeDBC();
// var_dump($result);


?>
</pre>
