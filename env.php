<?php

declare(strict_types = 1);
defined('IN_SYS') || exit('ACC Denied');
return [
    'mail_Username' => '1771033392@qq.com',
    'mail_Password' => 'qwe123123',
    'mail_From' => '1771033392@qq.com',
    'mail_Host' => 'smtp.qq.com',
    
    'mail_Username__yh' => 'mailserver@shinehua.cn',
    'mail_Password__yh' => 'Yh150906',
    'mail_From__yh' => 'mailserver@shinehua.cn',
    'mail_Host__yh' => 'smtp.mxhichina.com',
    
    'DB_CONNECTION' => '_test',
    
    'db_user' => 'root',
    'db_host' => '127.0.0.1',
    'db_passwd' => 'root',
    'db_db'     => 'yh',
    
    /*
      |--------------------------------------------------------------------------
      | 多环境变量的选择
      |--------------------------------------------------------------------------
      |
      | selection 返回string, 将确定以上参数中生效的是哪个
      |
     */
    'selection' => function() {
        if (isset($_SERVER['HTTP_HOST']) && ( $_SERVER['HTTP_HOST'] === '121.196.222.40')) {
            return '__yh';
        }
        return '__dev';
    }
];
