<?php


/**
 * 路由控制器
 * Class Router
 */
class Router{

    //路由队列
    private static $FIFO = array();

    //默认URL路由参数
    private static $URLPATH;

    //当前运行的路由对象参数
    private static $laber = array();

    //唯一标记符
    private static $runOnce = true;

    /**
     * @return mixed
     */
    public static function getLaber(){
        return self::$laber;
    }

    /**
     * 功能组建, 在类构造方法初始化期间调用当前方法,可以阻止逻辑单元去调用类方法
     */
    public static function stopFunction(){
        self::$laber['function'] = null;
    }

    /**
     * 设置默认路由参数
     * @param array $label
     */
    public static function setDefaultURL(array $label){
        Router::$URLPATH = $label;
    }

    /**
     * 添加路由队列
     * @param array $label 队列的参数值
     */
    public static function addQueue(array $label){
        array_push(self::$FIFO, $label);
    }

    /**
     * 获取默认路由参数（也就是浏览器中的url）
     * @return array|bool
     */
    public static function getAppUrl(){
        $label = array(
            'pattern'=>'urlPath',
            'app' => null,
            'className' => null,
            'function' => null
        );

        if (isset($_GET['s'])) {
            $R = str_split($_GET['s']);
            unset($_GET['s']);
        } else if (isset($_SERVER['PATH_INFO'])) {
            $R = str_split(substr($_SERVER['PATH_INFO'], 1));
        } else {
            return $label;
        }

        $container = &$label['app'];
        foreach ($R as $i => $v){
            if($v === '-' && $label['className'] === null){
                $container = &$label['className'];
            }
            else if($v === '.' && $label['function'] === null){
                $container = &$label['function'];

                if($label['className'] === null){
                    $label['className'] = $label['app'];
                    $label['app'] = Router::$URLPATH['app'];
                }
            }else{
                $container.= $v;
            }
        }

        return $label;
    }

    /**
     * 运行路由队列
     */
    public static function runPool(){

        //为了防止多次调用路由列队,这里设置成只运行一次
        if(Router::$runOnce  === false){
            return;
        }else{
            Router::$runOnce  = false;
        }

        //循环路由列队
        while (true) {
            Router::$laber = array_shift(self::$FIFO);
            if (empty(Router::$laber)) {
                break;
            }else{
                Router::runApp();
            }
        }

    }

    /**
     * 队列 逻辑单元运行
     */
    private static function runApp(){

        if (Router::$laber['pattern'] == 'urlPath') {
            //空参数检查
            if(Router::$laber['app'] == null){
                if(Router::$URLPATH == null){
                    return;
                }else{
                    Router::$laber = Router::$URLPATH;
                }
            }
            $class_path = PATH_APP.Router::$laber['app'].'\\'.Router::$laber['className'].'.php';
            $namespace = str_replace('/', '\\', Router::$laber['app']);
        } else if (Router::$laber['pattern'] == 'classPath') {
            $class_path = PATH_ROOT . str_replace('/', '\\', Router::$laber['classPath']);
            $namespace = Router::$laber['namespace'];
        }

        $class = C(Router::$laber['className'], $class_path, $namespace);
        $class = new $class;
        if (Router::$laber['function'] != null) {
            $function = &Router::$laber['function'];
            $class->$function();
        }

    }

}

