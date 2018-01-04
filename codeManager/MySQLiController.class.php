<?php



//数据库信息和数据库连接————————————————————————————————————————————————————————————————————————————————————————————————————
require_once "DBInfo.php";

$dbr = new mysqli(MSC_DB_ADDRESS, MSC_DB_USER, MSC_DB_PASSWORD, MSC_DB_NAME);
if( $dbr->connect_error ){
	throw new Exception($dbr->connect_error);
}

//数据库信息和数据库连接结束————————————————————————————————————————————————————————————————————————————————————————————————







class MySQLiController
{
	private $dbr;
    function __construct($dbr)
    {
        $this->dbr = $dbr;
		mysqli_query($this->dbr,"set names utf8");
    }

    protected function getPrimaryKey($tableName)
    {
        $query = 'SHOW KEYS FROM ' . $tableName . ' WHERE Key_name = "PRIMARY"';
        $result = mysqli_query($this->dbr, $query);
        $aKeyInfo = $result->fetch_array();
        return $aKeyInfo['Column_name'];
    }

	protected function fetchAssocArray($result){
		$rows = array();
	    while( $row=$result->fetch_assoc() ){
	        $rows[] = $row;
	    }
	    return $rows;
	}


    // 属性 ——————————————————————————————————————————————————————————————————————————————————————————————————————————————————

    function __get($name)
    {
        switch($name)
        {
            case "version": // MySQL版本
            {
                $query = 'SELECT version()';
                $result = mysqli_query($this->dbr, $query);
                $aVersionInfo = $result->fetch_array();
                return $aVersionInfo["version()"];
            }
        }
    }

    //整体操作——————————————————————————————————————————————————————————————————————————————————————————————————————————————————

    //创建表
    /*本文件只需要设置 $tableMode 变量即可，参考以下格式
        '(
            entry_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name TEXT NOT NULL,
            age INT NOT NULL,
            id TEXT NOT NULL
        )'
    */
    public function createTable($tableName, $tableMode)
    {
        $query = 'CREATE TABLE ' . $tableName . $tableMode;
        if( $this->dbr->query( $query ) )
        {
            return true;
        }
        else
        {
            return $this->dbr->error;
        }
    }

    // 删除表
    public function dropTable( $tableName )
    {
        $query = 'DROP TABLE ' . $tableName;
        if( $this->dbr->query( $query ) )
        {
            return true;
        }
        else
        {
            echo 'could not drop the table';
            return $this->dbr->error;
        }
    }



    //备份数据库。默认备份全部数据库。如果需要备份部分表，传参表名数组
    public function backup( $tables = '*')
    {
        $data = "\n/*---------------------------------------------------------------".
                "\n  SQL DB BACKUP ".date("d.m.Y H:i")." ".
                "\n  HOST: {" . DB_ADDRESS . "}".
                "\n  DATABASE: {" . DB_NAME . "}".
                "\n  TABLES: {$tables}".
                "\n  ---------------------------------------------------------------*/\n";

        mysqli_query( $this->dbr, "SET NAMES `utf8` COLLATE `utf8_general_ci`"  ); // Unicode

        if($tables == '*')//get all of the tables
        {
            $tables = array();
            $result = mysqli_query( $this->dbr, "SHOW TABLES");
            while($row = $result->fetch_row())
            {
                $tables[] = $row[0];
            }
        }
        else
        {
            $tables = is_array($tables) ? $tables : explode(',',$tables);
        }

        foreach($tables as $table)
        {
            $data.= "\n/*---------------------------------------------------------------".
                    "\n  TABLE: `{$table}`".
                    "\n  ---------------------------------------------------------------*/\n";
            $data.= "DROP TABLE IF EXISTS `{$table}`;\n";
            $res = mysqli_query( $this->dbr, "SHOW CREATE TABLE `{$table}`");
            $row = $res->fetch_row();
            $data.= $row[1].";\n";

            $result = mysqli_query( $this->dbr, "SELECT * FROM `{$table}`");
            $num_rows = mysqli_num_rows($result);

            if($num_rows>0)
            {
                $vals = Array(); $z=0;
                for($i=0; $i<$num_rows; $i++)
                {
                    $items = $result->fetch_row();
                    $vals[$z]="(";
                    for($j=0; $j<count($items); $j++)
                    {
                        if (isset($items[$j]))
                        {
                            $vals[$z].= "'".$this->dbr->real_escape_string( $items[$j] )."'";
                        }
                        else
                        {
                            $vals[$z].= "NULL";
                        }
                        if ($j<(count($items)-1))
                        {
                            $vals[$z].= ",";
                        }
                    }
                    $vals[$z].= ")"; $z++;
                }
                $data.= "INSERT INTO `{$table}` VALUES ";
                $data .= "  ".implode(";\nINSERT INTO `{$table}` VALUES ", $vals).";\n";
            }
        }

        // create backup
        $backup_file = 'db-backup-'.time() .'.sql';

        // get backup
        $mybackup = $data;

        // save to file
        if( @file_put_contents($backup_file, $mybackup) )
        {
             return true;
        }
        else
        {
            echo '数据库备份失败。';
        }
    }


    //返回所有的数据库组成的数组
    public function showDatabases()
    {
        $query = 'SHOW DATABASES';
        $result = mysqli_query($this->dbr, $query);
		return $this->fetchAssocArray($result);
    }

    //返回数据库的所有表组成的数组
    public function showTables(){
        $query = 'SHOW TABLES';
		$result = mysqli_query($this->dbr, $query);
        return $this->fetchAssocArray($result);
    }

    //返回传递表格每列的模式信息组成的数组
    public function describeTable($tableName){
        $query = 'DESCRIBE ' . $tableName ;
		$result = mysqli_query($this->dbr, $query);
        return $this->fetchAssocArray($result);
    }

    /* TODO 不对
    //创建索引
    public function createIndex($tableName, $colName)
    {
        $query = 'ALTER TABLE ' . $tableName . ' ADD INDEX ' . $colName . ' (' . $colName . ')' ;
        echo $query . '<br />';
        //return
        mysqli_query($this->dbr, $query);
        var_dump( $this->dbr->error ) ;
    }

    //删除索引
    public function dropIndex($tableName, $colName)
    {
        $query = 'ALTER TABLE ' . $tableName . ' DROP INDEX ' . $colName;
        mysqli_query($this->dbr, $query);
        var_dump( $this->dbr->error ) ;
    }
    */


    //权限操作——————————————————————————————————————————————————————————————————————————————————————————————————————————————————
    //创建管理员。获得当前数据库的所有权限。
    public function createAdministrator($username, $password)
    {
        $query = ' GRANT ALL ON ' . DB_NAME . '.* TO "' . $username . '" IDENTIFIED BY "' . $password . '" WITH GRANT OPTION';
        return mysqli_query($this->dbr, $query);
    }

    //创建只读权限用户。只能读取当前数据库的信息
    public function createReadOnlyUser($username, $password)
    {
        $query = ' GRANT SELECT ON ' . DB_NAME . '.* TO "' . $username . '" IDENTIFIED BY "' . $password . '"';
        return mysqli_query($this->dbr, $query);
    }


    //读取区域——————————————————————————————————————————————————————————————————————————————————————————————————————————————————

    // 表结构相关——————————————————————————————————————————————————————————————————

    // 获得某列信息
    public function getColumnInfoArray($tableName, $sColumn ){
        $query = 'SHOW FIELDS FROM ' . $tableName . ' where Field ="' . $sColumn . '"';
        $result = mysqli_query($this->dbr, $query);
        return $result->fetch_array();
    }







    // 内容行相关——————————————————————————————————————————————————————————————————


    //获得总行数
    public function allLineNum($tableName, $where="")
    {
		if($where){
			$query = 'SELECT *  FROM ' . $tableName . ' WHERE ' . $where;
		}
		else{
			$query = 'SELECT *  FROM ' . $tableName;
		}
        return mysqli_num_rows(mysqli_query($this->dbr, $query) );
    }




    //查找重复
    //返回一个两项数组。
    //如果有重复的，第一项是查询结果数组，可以通过fetch_array来循环查看重复项；第二项是有多少种重复。
    //如果没重复是，数组第一项是null，第二项是0
    public function getDuplicate($tableName, $column )
    {
        $query = 'select ' . $column . ',count(*) as count from ' . $tableName . ' group by ' . $column . ' having count>1';
        $result = mysqli_query($this->dbr, $query);
        $repetitionNum = $result->num_rows;
        if( $repetitionNum > 0 )
        {
            return array( $result, $repetitionNum);
        }
        else
        {
            return array( null, 0);
        }
    }

    //取得符合WHERE条件的一个或多个row组成的数组
    public function getRow($tableName, $where )
    {
    	$query = 'SELECT * FROM ' . $tableName . ' WHERE ' . $where;
    	$result = $this->dbr->query( $query );
    	if( $result ){
			return $this->fetchAssocArray($result);
		}
		else{
			echo "<p>could not get rows</p>";
		}
    }

	// 获取所有行的数据
	/*
	 * @para $col 可以传入一个列名，结果将按照该列数据进行分类
	 *            例如一个用户学历表中，如果传入学历的列名，则返回的结果是按照不同
	 *                学历分组的用户信息行
	 */
	public function getAll($tableName, $col=""){
		$query = 'SELECT * FROM ' . $tableName;
    	$result = $this->dbr->query( $query );
    	if( $result ){
			if( empty($col) ){
				return $this->fetchAssocArray($result);
			}
			else{
				$grouped = array();
				while( $row=$result->fetch_array() ){
					if (isset($grouped[$row[$col]])) {
						$grouped[$row[$col]][] = $row;
					} else {
						$grouped[$row[$col]] = array($row);
					}
				}
				return $grouped;
			}
		}
		else{
			echo "<p>could not get rows</p>";
		}
	}

	// 某一列的所有不重复的值组成的数组
	/*
	 * 例如一列是保存学历，则该方法返回的结果是各种学历组成的数组
	 */
	public function getTypes($tableName, $col){
		$query = 'SELECT DISTINCT ' .$col. ' FROM ' .$tableName;
		$result = $this->dbr->query( $query );
		if( $result ){
			return $this->fetchAssocArray($result);
		}
		else{
			echo "<p>could not get types</p>";
		}
	}


    // 返回所有行组成的数组，该数组按照某一列的值来排序
    // $col参数是用来排序的列，默认降序排列，$asc参数如果为true，则为升序
    public function getDataByRank($tableName, $where="", $col, $asc=false)
    {
		if( trim($where) !== "" ){
			$where = ' WHERE ' . $where;
		}

		$asc = $asc ? "" : ' DESC';

        $query = 'SELECT * FROM ' . $tableName . $where . ' ORDER BY ' . $col . $asc;
        $result = $this->dbr->query( $query );
        if( $result ){
			return $this->fetchAssocArray($result);
		}
		else{
			echo json_encode($result);
		}
    }


    //特征值(最大、最小、平均)相关—————————————————————————————————————————————————
    //某列最大值的所在行
    public function getMaxRow($tableName, $column)
    {
        $query = 'SELECT * FROM ' . $tableName . ' WHERE ' . $column . '=' . $this->getMaxValue($tableName, $column);
        $result = mysqli_query( $this->dbr, $query );
        $row = $result->fetch_array();
        return  $row;
    }

    //某列最大值
    public function getMaxValue($tableName, $column){
        $query = mysqli_query($this->dbr, 'SELECT MAX(' . $column . ') FROM ' . $tableName);
        $row = $query->fetch_row();
        return $row[0];
    }

    //某列最小值的所在行
    public function getMinRow($tableName, $column){
        $query = 'SELECT * FROM ' . $tableName . ' WHERE ' . $column . '=' . $this->getMinValue($tableName, $column);
        $result = mysqli_query( $this->dbr, $query );
        $row = $result->fetch_array();
        return  $row;
    }

    //某列最小值
    public function getMinValue($tableName, $column){
        $query = mysqli_query($this->dbr, 'SELECT MIN(' . $column . ') FROM ' . $tableName);
        $row = $query->fetch_array();
        return $row[0];
    }

    //输出某一列平均值
    public function average($tableName, $column ){
        $query = mysqli_query($this->dbr, 'SELECT AVG(' . $column . ') AS average FROM ' . $tableName);
        $row =$query->fetch_array();
        return $row['average'];
    }

    //输出某一列的值大于或者大于等于某个值的行数。例如及格的人数。第三个可选参数是否闭区间
    public function getAboveLineNum($tableName, $column, $value, $closed=true ){
        if( $closed ){
            $query = 'SELECT * FROM ' . $tableName . ' WHERE ' . $column . '>=' . $value;
        }
        else{
             $query = 'SELECT * FROM ' . $tableName . ' WHERE ' . $column . '>' . $value;
        }
        if( $result = mysqli_query($this->dbr, $query) ){
            $overlineNum = 0;
            while($row = $result->fetch_array()){
                $overlineNum++;
            }
            return  $overlineNum;
        }
        else{
            return $this->dbr->error;
        }
    }





    //写入区域----------------------------------------------------------------------------------------------
	//插入新列  参数为新列模式字符串。类似于 'id INT UNSIGNED NOT NULL'
	public function insertColumn($tableName, $sColMode)
	{
		$query = 'ALTER TABLE ' . $tableName . ' ADD COLUMN ' . $sColMode;
		$result = $this->dbr->query( $query );
		if( $result ){
			return true;
		}
		else{
			return $this->dbr->error;
		}
	}

	//删除列
	public function dropColumn($tableName, $sColName){

		$query = 'ALTER TABLE ' . $tableName . ' DROP ' . $sColName;
		$result = $this->dbr->query( $query );
		if( $result )
		{
			return true;
		}
		else
		{
			return $this->dbr->error;
		}
	}

    // 修改列名
    public function columnRename($tableName, $sColumn, $sNewname){
        $aColumnInfo = $this->getColumnInfoArray($tableName, $sColumn);
        $sOldName = $aColumnInfo["Field"];
        $sType = $aColumnInfo["Type"];
        $query = 'ALTER TABLE ' . $tableName . ' CHANGE ' . $sOldName . ' ' . $sNewname . ' ' . $sType;
        return $result = mysqli_query( $this->dbr, $query );
    }

    // 修改列类型
    public function changeColumnType($tableName, $sColumn, $sType){
        $query = 'ALTER TABLE ' . $tableName . ' MODIFY ' . $sColumn . ' ' . $sType;
        return $result = mysqli_query( $this->dbr, $query );
    }

	//插入新行。第二个参数是列名数组，第三个参数是相应的值数组
	public function insertRow($tableName, $aCol, $aValue){
		$len = count( $aCol );
		$keys = '';
		$values = '';

		for($i=0; $i<$len; $i++){
			if( $i !== $len-1 ){
				$keys .= $aCol[$i] . ',';
				$values .= '"' . $this->dbr->real_escape_string($aValue[$i]) . '",';
			}
			else{
				$keys .= $aCol[$i] . '';
				$values .= '"' . $this->dbr->real_escape_string($aValue[$i]) . '"';
			}
		}
		$query  = 'INSERT INTO ' . $tableName . '(' . $keys . ') VALUES (' . $values . ')';
		$result = $this->dbr->query( $query );
		if( $result ){
			return true;
		}
		else{
			return $this->dbr->error;
		}
	}

	//删除行。参数是数组，包含一个或者多个项，每个项是一个WHERE子句
	public function deleteRow($tableName, $where){
		$query = 'DELETE FROM ' . $tableName . ' WHERE (' . $where . ')';
		$result = $this->dbr->query( $query );

		if( $this->dbr->affected_rows && $result ) // 删除没有出错且至少删了一行
		{
			return true;
		}
		else{
			return $this->dbr->error;
		}
	}

    //以某一列为标准，只保留该列值重复的若干行中的一行
    public function setUniqueByColumn($tableName, $col )
    {
        $primaryKey = $this->getPrimaryKey();
        $query = 'DELETE FROM ' . $tableName . ' USING ' . $tableName . ',' . $tableName . ' e1 WHERE ' . $tableName . '.' . $primaryKey . ' > e1.' . $primaryKey . ' AND ' . $tableName . '.' . $col . ' = e1.' . $col . '  ';
        $result = $this->dbr->query( $query );
        if( $result ){
            print "<p>duplicates has been delete</p>";

        }
        else{
            print "<p>could not delete duplicates</p>";
        }
    }

	//更新值。第二个参数是要更改的值所在的列，第三个参数是新值，第四个参数WHERE子句用来定位到所在行
    public function updateData($tableName, $aLocValueCol, $aNewValue, $where)
    {
        if( sizeof($aLocValueCol) !== sizeof($aNewValue)){
            throw new Exception('要更改的列数和提供的更改值数目不对应');
            return false;
        }
        /*function setUpdateStr( $value, $key )
        {
            $value . '="' .  $aNewValue[$key] . '",';
        }*/
        $sUpdate = '';
        foreach($aLocValueCol as $key=>$value){
            $sUpdate .= $value . '="' .  $aNewValue[$key] . '",';
        }
        $sUpdate = substr($sUpdate, 0, -1);//删除最后一个逗号
        $query = 'UPDATE ' . $tableName . ' SET ' . $sUpdate . ' WHERE ' . $where;
        $result = $this->dbr->query( $query );
        if( $this->dbr->affected_rows && $result ){
            return true;
        }
        else{
            return $this->dbr->error;
        }
    }

	// Increase or Decrease
	public function increase($tableName, $sCol, $where, $bDecrease=false, $nLimitation=null){

		$result = $this->getRow($tableName, $where);
		$row = $result[0];
		if( is_numeric($row[$sCol])){
			if($nLimitation===(int)$row[$sCol]){
				return $bDecrease ? 'min' : 'max';
			}
			else{
				$nIncrement = $bDecrease ? -1 : 1;
				$query = 'UPDATE ' . $tableName . ' SET ' . $sCol . '=' . ($row[$sCol]+$nIncrement) . ' WHERE ' . $where;
				$result = $this->dbr->query( $query );
				if( $this->dbr->affected_rows && $result ){
					return true;
				}
				else{
					return $this->dbr->error;
				}
			}
		}
		else{
			return "not a number";
		}
	}


	// 将一列改为同一个值
	public function unifyColumn($tableName, $sCol, $value){
		$query = 'UPDATE ' . $tableName . ' SET ' . $sCol . '=' . $value;
        $result = $this->dbr->query( $query );
        if( $result ){
            return true;
        }
        else{
            return $this->dbr->error;
        }
	}
}

?>
