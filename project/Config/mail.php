<?php
/**
 * 本配置将直接赋值给 new PHPMailer 对象
 * 更改 : 你可以在任何地方重新定义这些属性, 因为,他们都是 public, 在你不定义的时候这些就是缺省值
 * 新增 : 依照 PHPMailer 的实例, 你可以加入任何有效属性在此, 比如 'SMTPKeepAlive' => true,
 */
return [
    /*
    |--------------------------------------------------------------------------
    | 邮件服务用户名及密码 ( 邮箱密码不一定为登入密码 ) 
    |--------------------------------------------------------------------------
    |
    | If your SMTP server requires a username for authentication, you should
    | set it here. This will get used to authenticate with your server on
    | connection. You may also set the "password" value below this one.
    |
    */
    'Username' => '1771033392@qq.com',
    'Password' => 'qwe123123',

    /*
    |--------------------------------------------------------------------------
    | 显示在邮件列表上的发件人是谁
    |--------------------------------------------------------------------------
    |
    | 发件人的地址 From 应该与 Username 保持一致
    |
    */
    'FromName' => '发件人名称',
    'From' => '1771033392@qq.com',   
    
    /*
    |--------------------------------------------------------------------------
    | Mail Driver
    |--------------------------------------------------------------------------
    |
    | Laravel supports both SMTP and PHP's "mail" function as drivers for the
    | sending of e-mail. You may specify which one you're using throughout
    | your application here. By default, Laravel is setup for SMTP mail.
    |
    | Supported: "smtp", "sendmail", "mailgun", "mandrill", "ses",
    |            "sparkpost", "log", "array"
    |
    */
    'Mailer' => 'smtp',

    /*
    |--------------------------------------------------------------------------
    | SMTP Host Address
    |--------------------------------------------------------------------------
    |
    | Here you may provide the host address of the SMTP server used by your
    | applications. A default option is provided that is compatible with
    | the Mailgun mail service which will provide reliable deliveries.
    |
    */
    'Host' => 'smtp.qq.com',

    /*
    |--------------------------------------------------------------------------
    | SMTP Host Port
    |--------------------------------------------------------------------------
    |
    | This is the SMTP port used by your application to deliver e-mails to
    | users of the application. Like the host we have set this value to
    | stay compatible with the Mailgun e-mail application by default.
    |
    */
    'Port' => 465,
    
    /*
    |--------------------------------------------------------------------------
    | 启用 SMTP 验证功能
    |--------------------------------------------------------------------------
    */
    'SMTPAuth' => true,
    
    /*
    |--------------------------------------------------------------------------
    | 加密方式
    |--------------------------------------------------------------------------
    |
    | Here you may specify the encryption protocol that should be used when
    | the application send e-mail messages. A sensible default using the
    | transport layer security protocol should provide great security.
    |
    */
    'SMTPSecure' => 'ssl',
    
    /*
    |--------------------------------------------------------------------------
    | 邮件字符编码
    |--------------------------------------------------------------------------
    |
    | 设定邮件字符编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8
    |
    */
    'CharSet' => 'UTF-8',
    
    /*
    |--------------------------------------------------------------------------
    | 邮件内容编码
    |--------------------------------------------------------------------------
    |
    | 你只有 text/html 以及 text/plain , 2种选择
    |
    */
    'ContentType' => 'text/html',

    /*
    |--------------------------------------------------------------------------
    | 邮件标题
    |--------------------------------------------------------------------------
    */
    'Subject' => '邮件标题',
    
    /*
    |--------------------------------------------------------------------------
    | 邮件内容
    |--------------------------------------------------------------------------
    |
    | 当接收邮件的客户端, 可以看到 html 时
    |
    */
    'Body' => '<h1>邮件内容</h1>',
    
    /*
    |--------------------------------------------------------------------------
    | 邮件内容
    |--------------------------------------------------------------------------
    |
    | 当接收邮件的客户端,不可以看到 html 时, 这种情况目前很少见
    |
    */
    'AltBody' => '邮件内容',
];