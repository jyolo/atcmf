<?php

use app\common\ExceptionHandle;
use RedisClient\RedisClient;

// 容器Provider定义文件
return [
    'think\exception\Handle' => ExceptionHandle::class,
    'redisClient' => RedisClient::class,
    'ip2Region' => Ip2Region::class
];
