<?php

namespace app\library;

use Baiy\ThinkAsync\Facade\Async;

/**
 * 异步方法触发条件，各种方法直接调用
 * 用来给程序触发异步队列的方法
 * 用法：如 A 控制器(模型，普通类) 调用此类里面的指定方法，用来触发异步业务逻辑。
 */
class AsyncDoo
{
    /**
     * 异步处理接口日志
     * @param array $data
     * @return string
     */
    public static function asyncApiLog(array $data):string
    {
        // 异步延迟执行 延迟20秒
        Async::exec(AsyncAction::class, 'asyncSaveApiLogToDB',$data);
        return '';
    }
}