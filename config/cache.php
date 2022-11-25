<?php

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'redis'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // 更多的缓存连接
        'redis' =>  [
            'type'  =>  'redis',
            'host'  =>  Env::get('redis.host','127.0.0.1'),
            'pass'  =>  Env::get('redis.pass',''),
            'port'  =>  Env::get('redis.port',6379),
            'select'  =>  Env::get('redis.select',8),
            'expire'  =>  Env::get('redis.expire',1440)
        ]
    ],
];
