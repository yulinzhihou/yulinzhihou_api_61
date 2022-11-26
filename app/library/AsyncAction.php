<?php

namespace app\library;

use app\admin\model\ApiLog;

/**
 * 异步类，走队列
 * 业务逻辑处理类，主要对接异步队列监听
 */
trait AsyncAction
{

    /**
     * 异步处理接口访问日志到数据表
     */
    public static function asyncSaveApiLogToDB(array $data):void
    {
        // 进来先判断是否有传数据
        if (!empty($data)) {
            // 接口日志
            ApiLog::create($data);
        }
    }


    /**
     * 异步处理失败传到ERP的数据
     */
    public static function asyncDoFailData():void
    {

    }

}