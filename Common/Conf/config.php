<?php
return array(
    /* 数据库 */
    'DB_TYPE'               =>  'mysql',    // 数据库类型
    'DB_HOST'               =>  '', // 服务器地址
    'DB_NAME'               =>  '', // 数据库名
    'DB_USER'               =>  '', // 用户名
    'DB_PWD'                =>  '', // 密码
    'DB_PORT'               =>  '', // 端口
    'DB_PREFIX'             =>  '', // 数据库表前缀

    'TMPL_ENGINE_TYPE'      =>  'Smarty',   // 模板引擎

    'MODULE_ALLOW_LIST'     =>  array('Admin', 'Gii'),  // 允许访问模块

    'SPHINX_HOST'           =>  '', // coreseek服务器ip或主机名
    'SPHINX_PORT'           =>  '', // coreseek服务器端口

    'UPLOAD_PATH'           =>  '', // 上传图片根目录,相对于index.php
    'LOCK_PATH'             =>  '', // 数据表锁文件目录,相对于index.php

    /* 发邮件 */
    'MAIL_USER'             =>  '', // 用户名
    'MAIL_PWD'              =>  '', // 密码
    'MAIL_HOST'             =>  '', // 邮件服务器ip或主机名

    /* 验证码 */
    'CAPTCHA_OPTION'        =>  array(
        'imageH'    =>  30,
        'imageW'    =>  100,
        'length'    =>  4,
        'fontSize'  =>  14
    )
);