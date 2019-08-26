<?php


class CLI{

    public function __construct(){
        self::set_HTTP_ROUTE();
    }

    /**
     * 设置常量 HTTP_ROUTE
     * 作为 视图模型的相对文件目录
     */
    private function SET_HTTP_ROUTE(){

        if(!defined('HTTP_ROUTE')){
            $i = 0;
            $indexs = array();
            while (isset($_SERVER['PHP_SELF'][$i])){
                if($_SERVER['PHP_SELF'][$i] == '\\'){
                    array_push($indexs,$i);
                };
                $i++;
            }
            $length = count($indexs)-1;
            $diff = $indexs[$length] - $indexs[$length-1] +1;
            define('HTTP_ROUTE',substr($_SERVER['PHP_SELF'],$indexs[$length-1],$diff));
        }

    }

}

