<?php
const PATH_ROOT  = __DIR__.'\\';
const PATH_PACK = PATH_ROOT.'violet\\';
const PATH_LIBRARY = PATH_PACK.'library\\';
const PATH_SUBSYSTEM = PATH_PACK.'subsystem\\';
const PATH_THROWABLE = PATH_PACK.'throwable\\';
const PATH_APP =  PATH_ROOT.'app\\';
const PATH_RUNTIME = PATH_ROOT.'runtime\\';

//加载核心组件
require PATH_THROWABLE.'ThrowableTpl.class.php';
require PATH_THROWABLE.'WarningController.class.php';
require PATH_THROWABLE.'ExceptionController.class.php';
require PATH_LIBRARY.'Common.php';
require PATH_LIBRARY.'Router.class.php';

/**
 * Class ARGS
 * 核心系统的参数配置
 */
class ARGS{

    /**
     * 设置 是否强制刷新视图缓存
     * @var bool
     */
    public static $TemplateCacheFlush = true;

    /**
     * 错误信息 的详细级别
     * 0 不输入任何错误信息
     * 1 打印有限的错误信息
     * 2 打印完整的错误信息
     * @var int
     */
    public static $PUT_PATTERN = 2;

    /**
     * 错误信息的存储目录
     * @var string
     */
    public static $PUT_PATH = PATH_RUNTIME.'error\\';

    /**
     * 临时文件存取目录
     * @var string
     */
    public static $FILE_TPM_PATH = PATH_RUNTIME.'tpm\\';

}

//警告信息处理
set_error_handler('Throwable\WarningController::create');

//致命错误处理
set_exception_handler('Throwable\ExceptionController::create');

//类加载器,自动加载子系统
spl_autoload_register(function ($class_name) {
    $class_path = PATH_PACK.$class_name.'.class.php';
    if(file_exists($class_path)){
        include $class_path;
    }
});

//数据库连接参数
define('PDO_LINK',array(
    'DB_TYPE'  => 'mysql',//唯一支持的数据库
    'DB_USER'  => 'chenqingyun',//账户名
    'DB_PWD'   => 'chenYun201',//登录密码
    'DB_HOST'  => 'localhost',//数据IP地址
    'DB_PORT'  => '3306',//数据库 访问端口
    'DB_NAME'  => 'javaweb',//数据库 库名
    'DB_CHARSET'=>'utf8',//数据交互 编码
    'DB_LONG'=>true,//开启长连接  true 开启 || false 关闭
));


if(PHP_SAPI == "cli"){

    Router::addQueue( array(
        'pattern' => "classPath",
        'classPath' => 'app/receive/controller/Macro',
        'className' => 'Macro',
        'namespace' => 'receive',
        'function' => 'testOne',
    ) );

}
else{

    if($_SERVER['SERVER_ADDR'] !== '192.168.0.189'){
        ARGS::$TemplateCacheFlush = false;
        ARGS::$PUT_PATTERN = 1;
    }

    //html相对项目路径,通常为模板提供路径变量
    define('HTTP_ROUTE',substr($_SERVER['SCRIPT_NAME'],0,-9));

    //设置默认URL路由 , 可选项
    Router::setDefaultURL(array(
        'pattern'=>'urlPath',
        'app'=>'page',
        'className'=>'Home',
        'function'=>'html'
    ));

    //将路由参数加入列队
    Router::addQueue( Router::getAppUrl() );

}

//运行项目
Router::runPool();