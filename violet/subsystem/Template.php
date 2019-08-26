<?php
namespace subsystem;

class Template{

    private $data = array();

    private $readPath;

    private $cacheDir;

    private $cacheName;

    private $cacheFile;

    /**
     * 视图模板
     * @param   $readPath       静态HTML文件
     */
    public function __construct($readPath){
        $this->readPath = $readPath;
        $lager = \Router::getLaber();
        if( $lager['pattern'] === 'urlPath'){
            $this->cacheName = "{$lager['className']}`{$lager['function']}.html";
            $this->cacheDir = PATH_RUNTIME."{$lager['app']}\\html\\";
            $this->cacheFile = $this->cacheDir.$this->cacheName;
        }
    }

    /**
     * @param string $cacheName
     */
    public function setCacheName(string $cacheName): void{
        $this->cacheName = $cacheName;
        $this->cacheFile = $this->cacheDir.$cacheName;
    }

    public function setData(array $data){
        $this->data = $data;
        return $this;
    }

    public function display(){
        if(\ARGS::$TemplateCacheFlush){
            self::cache_file();
        }else{
            file_exists($this->cacheFile) or self::cache_file();
        }
        include $this->cacheFile;
    }

    /**
     * 模板文件缓存
     */
    private function cache_file(){
        $cache_dir = $this->cacheDir;
        if(!file_exists($cache_dir)){
            mkdir($cache_dir,0777,true);
        }
        file_put_contents($this->cacheFile, self::ve_coding());
    }

    /**
     * 解析 模板文件
     * @param string|null $Path
     * @return string
     */
    private function ve_coding(string $Path = null) : string {
        if($Path == null){
            $Path = $this->readPath;
        }

        $template = preg_replace_callback('/{{\s*(.*)\s*}}/',function ($v1){
            //获取{{}}包裹的字符串
            $string = $v1[1];

            if(strpos($string,'ve.for') > -1){
                //for 循环标签
                return self::ve_for($string);
            }
            elseif($string[0] == "$"){
                //单一变量
               $key = substr($string,1);
               if(isset($this->data[$key])){
                   return '<?php echo $data['.$key.'] ?>';
               }else{
                   return '';
               }
            }
            elseif(strpos($string,'template')  > -1){
                //模板替换
                $path = preg_replace('/^template\s*/','',$string);
                if(is_string($path) && strlen($path) > 0){
                    $path = str_replace('/','\\',PATH_ROOT.$path);
                    return self::ve_coding($path);
                }else{
                    return $v1[0];
                }
            }
            else{
                //字符串或常量
                if(defined($v1[1])){
                    return constant($v1[1]);
                }else{
                    return $v1[0];
                }
            }
        },file_get_contents($Path));

        return $template;
    }

    /**
     * for 循环标签解析
     * @param string $str
     * @return string
     */
    private function ve_for(string $str){

        //判断当前元素标签,是否包含循环语法
        preg_match('/ve.for="?\$?(\\w+)"?/',$str,$ma);


        if(!empty($ma) && isset($this->data[$ma[1]])){

            $str = preg_replace('/\\s?ve.for=\"\$\\w+\"/','',$str);

            return sprintf('<?php foreach($this->data[\'%s\'] as $key => $item){ echo "%s";} ?>',$ma[1],$str);
        }else{
            return '';
        }
    }

}