<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 * 访问日志模型
 * @mixin \think\Model
 */
class ApiLog extends Base
{
    protected $schema = [
        "id"	    =>	"int",
        "admin_id"	=>	"int",
        "user_agent"=>	"string",
        "admin_name"=>	"string",
        "version"   =>	"string",
        "method"    =>	"string",
        "code"	    =>	"string",
        "title"	    =>	"string",
        "url"	    =>	"string",
        "params"	=>	"string",
        "result"	=>	"string",
        "sql"	    =>	"string",
        "controller"=>	"string",
        "action"	=>	"string",
        "ip"	    =>	"string",
        "waste_time"	=>	"float",
        "create_time"	=>	"int",
        "update_time"	=>	"int",
    ];
}
