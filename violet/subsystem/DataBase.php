<?php
namespace subsystem;

/**
 *
 * Class DataBase
 * @package Library
 */
class DataBase{

    private $pdo = null;
    private $sql = null;

    private $where_field = null;

    public function __construct(\PDO $PDO){
        $this->pdo = $PDO;
    }

    public function setPDO(\PDO $PDO){
        $this->pdo = $PDO;
    }

    public function getPDO(){
        return $this->pdo;
    }

    public function getSql(){
        $sql = "SELECT {$this->sql['field']} FROM `{$this->sql['from']}` {$this->sql['where']} {$this->sql['order']} LIMIT 1";
        return $sql;
    }

    /**
     * 设置数据库表名称
     * @param String $name  表名称
     * @return $this
     */
    public function setfrom(String $name){
        $this->sql = array(
            'field' => "*",
            'from' => $name,
            'where' => null,
            'order' => null,
            'limit' => null,
            'group' => null
        );
        return $this;
    }

    public function where($arr){
        if($arr === 1){
            $where = 1;
        }else{
            $where = self::where_array($arr);
        }
        $this->sql['where'] = 'WHERE '.$where;
        return $this;
    }

    private function where_array($arr){
        $str = '';
        $rel = ' AND ';
        foreach ($arr as $key => $item){
            if (is_array($item)){
                if(is_string($key)){
                    $this->where_field = $key;
                }
                if($str === ''){
                    $str .= self::where_array($item);
                }else{
                    $str .= $rel.self::where_array($item);
                }
            }else{
                if(is_string($key)){
                    if($str === ''){
                        $str .= "`$key` = '{$item}'";
                    }else{
                        $str .= $rel."`$key` = '{$item}'";;
                    }
                }
                else{
                    if($item == 'and'){
                        $rel = " AND ";
                    }elseif ($item == 'or'){
                        $rel = " OR ";
                    }else{
                        return self::where_atom($arr);
                        break;
                    }
                }
            }
        }
        return "( {$str} )";
    }

    private function where_atom($atom){
        $name = array_shift($atom);
        switch ($name){
            case "eq":
                return "`{$this->where_field}` = '{$atom[0]}'";
            case "neq":
                return "`{$this->where_field}` <> '{$atom[0]}'";
            case "lt":
                return "`{$this->where_field}` < '{$atom[0]}'";
            case "nlt":
                return "`{$this->where_field}` <= '{$atom[0]}'";
            case "gt":
                return "`{$this->where_field}` > '{$atom[0]}'";
            case "ngt":
                return "`{$this->where_field}` >= '{$atom[0]}'";
            case "linke":
                return "`{$this->where_field}` LIKE '%{$atom[0]}%'";
            case "nlinke":
                return "`{$this->where_field}` NOT LIKE '%{$atom[0]}%'";
            case "null":
                return "`{$this->where_field}` IS NULL ";
            case "nnull":
                return "`{$this->where_field}` IS NOT NULL ";
            case "between":
                return "`{$this->where_field}` BETWEEN '{$atom[0]}' AND '{$atom[1]}'";
            case "in":
                $in = implode(',', array_map(function ($e){return "'{$e}'";},$atom));
                return "`{$this->where_field}` IN ($in)";
            case "nin":
                $in = implode(',', array_map(function ($e){return "'{$e}'";},$atom));
                return "`{$this->where_field}` NOT IN ($in)";
            default:
                return '';
                break;
        }
    }

    public function field($str){
        $this->sql["field"] = " {$str} ";
        return $this;
    }

    public function order($name,$value = "DESC"){
        if(is_string($name)){
            $this->sql['order'] = "ORDER BY `{$name}` {$value} ";
        }
        return $this;
    }

    /**
     * 查询结果返回数量限制，空参默认为 1
     * @param int $v1      数量 || 开始位置
     * @param int $v2      数量
     * @return $this
     */
    public function limit($v1=null,$v2=null){

        if( is_int($v1) && is_null($v2)){
            $str = $v1;
        }
        elseif ( is_int($v1) && is_int($v2) ){
            $str = "{$v1},{$v2}";
        }
        else{
            $str = 1;
        }

        $this->sql["limit"] = "LIMIT {$str}";
        return $this;
    }

    /**
     * @param bool $distinct
     * @return array
     */
    public function select($distinct = false){

        $sql = "{$this->sql['field']} FROM `{$this->sql['from']}`";
        if($distinct){
            $sql = "SELECT DISTINCT $sql {$this->sql['where']} {$this->sql['order']} {$this->sql['limit']}";
        }else{
            $sql = "SELECT $sql {$this->sql['where']} {$this->sql['order']} {$this->sql['limit']}";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * 只获取一条数据
     * @return mixed
     */
    public function find(){
        $sql = "SELECT {$this->sql['field']} FROM `{$this->sql['from']}` {$this->sql['where']} {$this->sql['order']} LIMIT 1";
        return $this->pdo->query($sql)->fetch();
    }

    public function add($data,$type = 'C'){
        $head = self::getInsertType($type);
        $keys = array_keys($data);
        $values = array_values($data);

        $keysString = self::getFieldString($keys);

        if(is_array($values[0])){
            //最大列
            $maxRow = count($values[0]);

            //最大行
            $maxCol = count($keys);

            $valuesString = '';
            for ($i = 0 ; $i < $maxRow;$i++){
                $str = '(';
                for ($c = 0; $c < $maxCol; $c++){
                    $value = $values[$c][$i];
                    if($value === null){
                        $str .= "NULL,";
                    }
                    else if(is_string($value)){
                        $str .= "'{$value}',";
                    } else{
                        $str .= "{$value},";
                    }
                }
                $valuesString .= substr_replace($str,'),',-1);
            }
            $valuesString = substr_replace($valuesString,'',-1);
        }else{
            $valuesString = self::getRowString($values);
        }

        $sql = "{$head} `{$this->sql['from']}` $keysString VALUES $valuesString";
        return  $this->pdo->exec($sql);
    }

    /**
     * 批量数据添加 模式二
     * @param $keys [key,...]
     * @param $rows [[],...]
     * @param string $type  重复数据处理模式， A报错 B放弃 C覆盖
     * @return int
     */
    public function addTwo($keys,$rows,$type = 'C'){

        //组装数据表字段
        $field = $this->getFieldString($keys);

        //组装 表数据
        $addString = '';
        foreach ($rows as $row){
            $tr = self::getRowString($row);
            $addString .= "{$tr},";
        }
        $addString = substr_replace($addString,'',-1);

        //生成添加模式的表头
        $head = self::getInsertType($type);
        $sql = "{$head} `{$this->sql['from']}` $field VALUES $addString";

        return $this->pdo->exec($sql);
    }

    /**
     * UpData 更新数据
     * @param $data 数据内容
     * @param string $type  更新模式
     * @return int  操作成功有效条数
     */
    public function set($data,$type="A"){
        $length = count($data);
        $i = 0;
        $str = '';
        foreach ($data as $key => $value){
            if(++$i == $length){
                if(is_string($value)){
                    $str .= "`$key`='$value'";
                }
                else if($value === null){
                    $str .= "`$key`= NULL ";
                }
                else{
                    $str .= "`$key`= $value ";
                }
            }else{
                if(is_string($value)){
                    $str .= "`$key`='$value',";
                }
                else if($value === null){
                    $str .= "`$key`= NULL,";
                }
                else{
                    $str .= "`$key`= $value,";
                }
            }
        }
        if($type === "A"){
            $sql = "UPDATE `{$this->sql['from']}` SET  $str {$this->sql['where']}";
        }else{
            $sql = "UPDATE IGNORE `{$this->sql['from']}` SET  $str {$this->sql['where']}";
        }

        return  $this->pdo->exec($sql);
    }

    /**
     * @param bool $boole   清空数据表
     * @return bool|int
     */
    public function delete($boole = false){
        if($boole === true){
            $sql = "TRUNCATE TABLE `{$this->sql['from']}`";
        }
        else{
            if($this->sql['where'] == null){
                return false;
            }else{
                $sql ="DELETE FROM `{$this->sql['from']}` {$this->sql['where']}";

            }
        }
        return  $this->pdo->exec($sql);
    }

    /**
     * 完全删除数据表 - 连同数据表结构一起删除
     * @param bool $bool
     * @return int
     */
    public function dropTable(bool $bool = false){
        if($bool){
            $sql = "DROP TABLE IF EXISTS  `{$this->sql['from']}`";
            return $this->pdo->exec($sql);
        }
        return -1;
    }

    /**
     * 获取自增长 ID 值
     * @return string
     */
    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }

    /**
     * 返回 SQL 头部语句
     * @param string $type   A报错 B放弃 C覆盖
     * @return string       SQL头部语句
     * @throws \Error
     */
    private function getInsertType(string $type){
        switch ($type){
            case 'A':
                return 'INSERT INTO';
            case 'B':
                return 'INSERT IGNORE INTO';
            case 'C':
                return 'REPLACE INTO';
            default:
                throw new \Error('Type 类型不明确');
        }
    }

    /**
     * SQL 字段数组序列化
     * @param array $row    一维数组
     * @return string
     */
    private function getFieldString(array $row){
        $str = '(';
        foreach ($row as $key){
            if($key === null){
                $str .= "NULL,";
            }else{
                $str .="`{$key}`,";
            }
        }
        return substr_replace($str,')',-1);
    }

    /**
     * SQL 单行数组序列化
     * @param array $row
     * @return string
     */
    private function getRowString(array $row){
        $str = '(';
        foreach ($row as $value){
            if($value === null){
                $str .= "NULL,";
            }
            else if(is_string($value)){
                $str .= "'{$value}',";
            } else{
                $str .= "{$value},";
            }
        }
        return substr_replace($str,')',-1);
    }
}