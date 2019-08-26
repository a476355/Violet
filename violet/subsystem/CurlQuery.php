<?php

namespace subsystem;


/**
 * CURL 快速操作的工具类 当前类中所有属性都是 curl_setopt(); 需要设置的参数
 * Class CurlQuery
 * @package subsystem
 */
class CurlQuery{
    private $ch = null;

    /**
     * 在HTTP请求中包含一个"User-Agent: "头的字符串。
     * CURLOPT_USERAGENT
     * @var string
     */
    private $_useragent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';

    /**
     * 需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候。
     * CURLOPT_URL
     * @var String
     */
    private $_url;

    /**
     * 允许 cURL 函数执行的最长秒数。
     * CURLOPT_TIMEOUT
     * @var int
     */
    private $_timeout;

    /**
     * 在尝试连接时等待的秒数。设置为0，则无限等待。
     * CURLOPT_CONNECTTIMEOUT
     * @var int
     */
    private $_timewat;

    /**
     * TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
     * CURLOPT_RETURNTRANSFER
     * @var Boolean
     */
    private $_returntransfer = true;

    /**
     * TRUE 时会发送 POST 请求，类型为：application/x-www-form-urlencoded
     * CURLOPT_POST
     * @var Boolean
     */
    private $_postdata;

    /**
     * 设定 HTTP 请求中"Cookie: "部分的内容。多个 cookie 用分号分隔，分号后带一个空格
     * CURLOPT_COOKIE
     * @var String
     */
    private $_cookie;

    /**
     * TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。
     * CURLOPT_FOLLOWLOCATION
     * @var Boolean
     */
    private $_followlocation = true;

    public function __construct(){
        $this->init();
    }

    public function init(){
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_AUTOREFERER ,true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER ,true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->_useragent);
    }


    public function query() :string {

        if($this->_postdata == null){
            curl_setopt($this->ch,CURLOPT_POST,false);
            $html = curl_exec($this->ch);
        }else{
            curl_setopt($this->ch,CURLOPT_POST,true);
            curl_setopt($this->ch,CURLOPT_POSTFIELDS,$this->_postdata);
            $html = curl_exec($this->ch);
            $this->_postdata = null;
        }

        //检查求情状态码。
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if($httpCode == 200){
            return $html;
        }
        else{
            print_r($html);
            return 404;
        }

    }

    public function setUseragent(string $useragent){
        $this->_useragent = $useragent;
        curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
    }

    public function setUrl(String $url){
        if(strstr($url,'https:')){
            curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($this->ch,CURLOPT_SSL_VERIFYHOST,0);
        }else{
            curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER,TRUE);
            curl_setopt($this->ch,CURLOPT_SSL_VERIFYHOST,2);
        }

        curl_setopt($this->ch, CURLOPT_URL ,$url);
        $this->_url = $url;
    }

    public function setTimeout(int $timeout){
        $this->_timeout = $timeout;
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
    }

    public function setTimewat(int $timewat){
        $this->_timewat = $timewat;
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timewat);
    }

    public function setPostdata($postdata){
        $this->_postdata = $postdata;
    }

    public function setCookie(String $cookie){
        $this->_cookie = $cookie;
        curl_setopt( $this->ch,CURLOPT_COOKIE,$cookie);
    }

    public function setReturntransfer(bool $returntransfer){
        $this->_returntransfer = $returntransfer;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER ,$returntransfer);
    }

    public function setFollowlocation(bool $followlocation){
        $this->_followlocation = $followlocation;
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION ,$followlocation);
    }

}