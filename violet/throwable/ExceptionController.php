<?php
namespace Throwable;

class ExceptionController extends ThrowableTpl{

    public function __construct(\Throwable $error){
        $this->setHost();
        $this->data['h1'] = "Fatal Error";
        $this->data['nubmer'] = $error->getCode();
        $this->data['info'] = $error->getMessage();
        $this->data['file'] = $error->getFile();
        $this->data['line'] = $error->getLine();
        $this->data['code'] = trim(file($this->data['file'])[$this->data['line']-1]);

        if($this->data['code'] == '$PDO = new \PDO($dsn, $link[\'DB_USER\'], $link[\'DB_PWD\'] ,$set);'){
            $this->data['info'] = '数据库创建错误！';
            $this->data['context'] = '数据库创建错误！';
        }else{
            $this->data['context'] = self::getTraceContext($error->getTrace());
        }
    }

    public static function create(\Throwable $error){
        $gw = new static($error);
        $gw->outHtml();
        die();
    }

    private function getTraceContext(array $arr){
        $args = [];
        foreach ($arr as $item){
            if(!empty($item['args']) && isset($item['file'])){
                $li['file'] = $item['file'];
                $li['line'] = $item['line'];
                if(isset($item['class'])){
                    $li['function'] = $item['class'].$item['type'].$item['function'].'($args...)';
                }else{
                    $li['function'] = $item['function'].'($args...)';
                }
                $li['code'] = trim(file($item['file'])[$item['line']-1]);
                $li['args'] = $item['args'];
                array_push($args,$li);
            }
        }
        return $args;
    }

}