    #通过类路径的形式添加路由，通常运用在 CLI 模式中
	Router::addQueue( array(
        'pattern' => "classPath",
        'classPath' => 'app/port/JavaJsonDatanData.php',
        'className' => 'JavaJsonData',
        'namespace' => 'port',
        'function' => 'hello',
    ));