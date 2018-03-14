<?php

// 数据库配置
return [

    'mysql' => [
        // 数据源格式
        'dsn'       => 'mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=wheng',
        // 数据库用户名
        'username'  => 'root',
        // 数据库密码
        'password'  => 'square82',
    ],

    'redis' => [
        // 主机
        'host'     => '127.0.0.1',
        // 端口
        'port'     => 6379,
        // 数据库
        'database' => 0,
        // 密码
        'password' => '',
    ],

];
