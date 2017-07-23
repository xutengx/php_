<?php
// 有问题请联系 QQ 1771033392
$GLOBALS['statistic'] = array(
    '_beginTime' => microtime(true),
    '_beginMemory' => memory_get_usage()
);
// 入口文件名 eg:index.php 
defined('IN_SYS') || define('IN_SYS', substr(str_replace('\\','/',__FILE__),strrpos(str_replace('\\','/',__FILE__),'/')+1));

// 入口文件目录在服务器的绝对路径 eg:/mnt/hgfs/www/git/php_/project/ 
define('ROOT', str_replace('\\','/',dirname(__FILE__)).'/');

// 是否命令模式 eg:true 
define('CLI', (php_sapi_name() !== 'cli') ? false : true);

// 自动加载类
require (ROOT . 'Main/Core/Integrator.php');
// 公用方法
require (ROOT . 'Main/Core/Func.php');
// 执行
\Main\Core\Route::Start();