<?php

class Generator{


    private $aChars; // 用来生成code的字符
    private $nLen; // 每个code的长度，包含前缀

    private $sql;
    private $dbr;
    private $table;


    function __construct(){

        $this->aChars = str_split('23456789abcdefghjkmnpqrstuvwxyz');
        $this->nCodeLen = 8;

        require('MySQLiController.class.php');
        $this->sql = new MySQLiController( $dbr );
        $this->dbr = $dbr;
        $this->table = 'codes';
    }



    // 检查当批码的配置
    private function checkConfig(){
        if(!trim(PREFIX)){
            echo 'No prifix';
            throw new RuntimeException('No prifix');
        }
        if(strlen(trim(PREFIX))>4){
            echo 'Prifix is too long';
            throw new RuntimeException('Prifix is too long');
        }
        if(!is_int(AMOUNT) || AMOUNT<=0){
            'No amount';
            throw new RuntimeException('No amount');
        }
        if(!trim(MERCHANT_ID)){
            echo 'No merchantID';
            throw new RuntimeException('No merchantID');
        }
        if(strlen(trim(MERCHANT_ID))>16){
            echo 'merchantID is too long';
            throw new RuntimeException('merchantID is too long');
        }
    }


    // 现有的所有的code
    private function getCode(){
        $result = $this->sql->getAll($this->table);
        $code = array();
        foreach($result as $value){
            $codes[] = $value['code'];
        }
        return $codes;
    }


    // 生成一个随机码
    private function generateOne(){
        $nCharAmount = count($this->aChars);
        $nIndex = -1; // 随机数在$this->aChars中的位置
        $sCode = PREFIX;    // 生成的码，初始带前缀，随后添加随机字符

        for($i=0; $i<$this->nCodeLen-strlen(PREFIX); $i++){
            $nIndex = mt_rand(0, $nCharAmount-1);
            $sCode .= $this->aChars[$nIndex];
        }
        return $sCode;
    }


    // 批量生成不重复随机码
    private function generate(){

        $this->checkConfig();

        $aAlready = $this->getCode();
        $set =  $this->getCode(); // 已存在的后续生成的都要放进$set,$set中不存在重复的code
        $nAmount = AMOUNT + count($aAlready);

        while(count($set)<$nAmount){
            $newCode = $this->generateOne($this->aChars, $this->nCodeLen);
            if(!in_array($newCode, $set)){ // 只加入不重复的
                $set[] = $newCode;
            }
        }
        return array_slice($set, count($aAlready));
    }


    public function addCodes(){
        $codes = $this->generate();

        $aErr = array();

        foreach($codes as $value){
            $result = $this->sql->insertRow($this->table,
                    array('code', 'merchantID', 'WechatID', 'generationDate')
                    ,array($value, MERCHANT_ID, WECHAT_ID, date('Y-m-d')));
            if($result!==true){
                $aErr[] = array($result, $value);
            }
        }

        $return = array(
            'newCodes'=>$codes,
            'errCodes'=>$aErr
        );
        if(count($aErr)){
            $return['isErr'] = true;
        }
        else{
            $return['isErr'] = false;
        }

        return $return;
    }

    // 使一批code失效
    public function invalidate($aInvalidCode){
        $aErr = array();
        foreach($aInvalidCode as $code){
            $result = $this->sql->updateData($this->table, array('bad')
                                    , array('1'), 'code="'.$code.'"');
            if($result!==true){
                $aErr[] = array($result, $code);
            }
        }

        $return = array(
            'errCodes'=>$aErr
        );
        if(count($aErr)){
            $return['isErr'] = true;
        }
        else{
            $return['isErr'] = false;
        }

        return $return;
    }


    public function closeDBC(){
        $this->dbr->close();
    }
}

?>
