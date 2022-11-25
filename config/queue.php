<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    'default'     => 'redis',
    'connections' => [
        'sync'     => [
            'type' => 'sync',
        ],
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'jobs',
            'connection' => null,
        ],
        'redis'    => [
            'type'          => 'redis',
            'queue'         => 'gs_gm_admin_queue',
            'persistent'    => false,
            'host'          =>  Env::get('redis.host','127.0.0.1'),
            'password'      =>  Env::get('redis.pass',''),
            'port'          =>  Env::get('redis.port',6379),
            'select'        =>  Env::get('redis.select',8),
            'timeout'       =>  Env::get('redis.expire',1440)
        ],
    ],
    'failed'      => [
        'type'  => 'none',
        'table' => 'failed_jobs',
    ],
];
