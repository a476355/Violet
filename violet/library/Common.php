<?php


/**
 * @param $className
 * @param null $path
 * @param null $namespace
 * @return string
 */
function C($className, $path = null, $namespace = null){
    if ($namespace == null) {
        $c = $className;
    } else {
        $c = '\\' . $namespace . '\\' . $className;
    }
    include_once $path;
    return $c;
}

/**
 * 获取数据操作模型
 * @return \subsystem\DataBase
 */
function M(){
    return new \subsystem\DataBase( PDO(PDO_LINK) );
}

/**
 * HTML提示中转页
 * @param string $text  提示的文本
 * @param string $url   转跳的页面
 * @param int $int      等待的时间（秒）
 */
function H($text='',$url='null',$int=10){
    include PATH_PACK.'TPL/HintPanel.html';
}

/**
 * 通过视图模板加载HTML文件
 * @param string $classPath
 * @param null $data
 * @param null $cacheName
 */
function T(string $classPath,$data = null,$cacheName = null){
    $readPath = str_replace('/', '\\', PATH_ROOT.$classPath );
    $temp = new \subsystem\Template($readPath);
    if($data != null){
        $temp->setData($data);
    }
    if($cacheName != null){
        $temp->setCacheName($cacheName);
    }

    $temp->display();
}

/**
 * 获取客户端IP地址
 * @return string
 */
function IP() {
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

/**
 * 字符串安全验证
 * @param $string
 * @param bool $isurl
 * @return string
 */
function CHECK_STR($string, $isurl = false){
    $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','',$string);
    $string = str_replace(array("\0","%00","\r"),'',$string);
    empty($isurl) && $string = preg_replace("/&(?!(#[0-9]+|[a-z]+);)/si",'&',$string);
    $string = str_replace(array("%3C",'<'),'<',$string);
    $string = str_replace(array("%3E",'>'),'>',$string);
    $string = str_replace(array('"',"'","\t",' '),array('“','‘',' ',' '),$string);
    return trim($string);
}

/**
 * PDO 对象创建工厂
 * @param array $link   PDO连接参数
 * @return PDO
 */
function PDO(array $link){
    $dsn = "{$link['DB_TYPE']}:dbname={$link['DB_NAME']};host={$link['DB_HOST']}:{$link['DB_PORT']}";
    $set = array(
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$link['DB_CHARSET']};",
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_PERSISTENT => $link['DB_LONG']
    );
    $PDO = new \PDO($dsn, $link['DB_USER'], $link['DB_PWD'] ,$set);
    return $PDO;
}

/**
 * 设置 404 错误警告
 */
function set404(){
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
}

/**
 * 设置 303 永久性转移
 * @param $url
 */
function set303(string $url){
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: '.$url);
}

/**
 * 输出 JSON 字符串
 * @param $data
 */
function putJson($data){
    header('Content-Type:application/json; charset=utf-8');
    if(is_string($data)){
        echo $data;
    }else{
        echo json_encode($data);
    }


}

/**
 * 将数组转换成满足 URL 格式的参数
 * @param array $arr
 * @param bool $urlen
 * @return bool|string
 */
function toUrlGetValue(array $arr,bool $urlen= false){
    $str = '';
    foreach ($arr as $k => $v){
        $str .= "$k=$v&";
    }

    if($urlen){
        return urlencode(substr($str,0,-1));
    }else{
        return substr($str,0,-1);
    }
}

/**
 * 二维数组 通过指定键去重
 * @param array $towDimension
 * @param string $key
 * @return array
 */
function hashArrayElement(array $towDimension,string $key){
    $unique = array();
    foreach ($towDimension as $index => $item){
        foreach ($unique as $v){
            if($v[$key] == $item[$key]){
                continue 2;
            }
        }
        array_push($unique,$item);
    }
    return $unique;
}

/**
 * CURL  类的快速运用
 * @param string $url   RUL地址
 * @param null $data    POST参数
 * @return string
 */
function request(string $url,$data = null){

    static $curlQuery = null;

    if($curlQuery === null){
        $curlQuery = new \subsystem\CurlQuery();
    }

    $curlQuery->setUrl($url);

    if(!empty($data)){
        $curlQuery->setPostdata($data);
    }else{
        $curlQuery->setPostdata(null);
    }

    return $curlQuery->query();

}

/**
 * 获取本机真实IP地址
 * @return string
 */
function getLocalIP(){
    $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
    exec("ipconfig", $out, $stats);
    if (!empty($out)) {
        foreach ($out AS $row) {
            if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                $tmpIp = explode(":", $row);
                if (preg_match($preg, trim($tmpIp[1]))) {
                    return trim($tmpIp[1]);
                }
            }
        }
    }
}