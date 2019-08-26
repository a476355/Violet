<?php

namespace Throwable;

abstract class ThrowableTpl{

    public $data = array(
        'h1'=>null,
        'code'=>null,
        'nubmer'=>null,
        'info'=>null,
        'file'=>null,
        'line'=>null,
        'context'=>null,
        'host'=>null,
        'date'=>null,
    );

    /**
     * ThrowableTpl constructor.
     * @param array $data
     */
    public function setHost(){
        if(PHP_SAPI == "cli"){
            $this->data['host'] = $_SERVER['PHP_SELF'];
        }else{
            if(isset($_SERVER['REQUEST_SCHEME'])){
                $this->data['host'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            }else{
                $this->data['host'] = $_SERVER['HTTP_HOST'].'://'.$_SERVER['REMOTE_ADDR'].$_SERVER['REQUEST_URI'];
            }
        }
        $this->data['date'] = date("Y-m-d H:i:s");
    }

    public function arrays(array $arr,$zero = true){
        $tr = '';
        foreach ($arr as $key => $value){
            if(is_array($value)){
                 $tr .= "<li>[$key] => Array( ".$this->arrays($value,false)." )</li>";
            }else{
                 $tr .= "<li>[$key] => $value</li>";
            }
        }
        if($zero){
            return "<ul>Array({$tr})</ul>";
        }else{
            return "<ul>{$tr}</ul>";
        }

    }

    public function outHtml(){

        //获取错误信息的HTML文件
        $html = $this->getXmlHtml();

        //将错误信息写入本地
        self::xml_as_put($html);

        //根据等级 优化错误输出的信息
        if (\ARGS::$PUT_PATTERN === 1){
            $this->data['code'] = '----';
            $this->data['file'] = '----';
            $this->data['line'] = '----';
            $this->data['context'] = '----';
            $this->data['host'] = '----';

            echo $this->getXmlHtml();
        }
        else if (\ARGS::$PUT_PATTERN === 2){
            echo $html;
        }
    }

    public function xml_as_put($html){
        if(!file_exists(\ARGS::$PUT_PATH)){
            mkdir(\ARGS::$PUT_PATH,0777,true);
        }
        $file_path = \ARGS::$PUT_PATH.date("YmdHis").'.html';
        file_put_contents($file_path,$html);
    }

    private function getXmlHtml(){
        return preg_replace_callback('/{{(.*)}}/',function ($arr){
            return preg_replace_callback('/\\$?(\\w+)/',function($item){
                if(isset($this->data[$item[1]])){
                    if(is_array($this->data[$item[1]])){
                        return self::arrays($this->data[$item[1]]);
                    }else{
                        return $this->data[$item[1]];
                    }
                }else{
                    if(defined($item[0])){
                        return constant($item[0]);
                    }else{
                        return $item[0];
                    }
                }
            },$arr[1]);
        },file_get_contents(PATH_PACK."TPL/Evergarden.html"));
    }

}