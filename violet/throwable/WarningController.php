<?php
namespace Throwable;

class WarningController extends ThrowableTpl {

    public static function create($v1,$v2,$v3,$v4,$v5){
        $gw = new static($v1,$v2,$v3,$v4,$v5);
        $gw->outHtml();
        die();
    }

    public function __construct($v1,$v2,$v3,$v4,$v5){
        $this->setHost();
        $this->data['h1'] = "Warning Info";
        $this->data['code'] = trim(file($v3)[$v4-1]);
        $this->data['nubmer'] = $v1;
        $this->data['info'] = $v2;
        $this->data['file'] = $v3;
        $this->data['line'] = $v4;
        if($v1 == 8){
            $this->data['context'] = self::IndexOutOfBoundsException($this->data['code'],$v5);
        }else{
            $this->data['context'] = 'NULL';
        }
    }

    private function IndexOutOfBoundsException($errcode,$errcontext){
        preg_match_all('/\$(\\w*)\[[\'\$\"]?(\\w*)[\'\$\"]?](?!\\s*\\S*=)/',$errcode,$info);
        if(!empty($info[1]) ){
            if(isset($info[2])){
                $keys = array_merge($info[1],$info[2]);
            }else{
                $keys = array_unique($info[1]);
            }
            $keys = array_intersect($keys, array_keys($errcontext));

            //合并有效上下文数据
            foreach ($keys as $key){
                $ErrArrays[$key] = $errcontext[$key];
            }

            //重置上下文数据
            if(isset($ErrArrays)){
                return $ErrArrays;
            }
        }
        return "NULL";
    }

}