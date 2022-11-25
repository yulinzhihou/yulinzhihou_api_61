<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class ApiLog extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|'	=>	['number','integer'],
		'admin_id|管理员ID'	=>	['egt:0','number','integer'],
		'admin_name|管理员名称'	=>	['length:0,127'],
		'user_agent|请求代理'	=>	['length:0,254'],
		'app_name|应用名称'	=>	['length:0,29'],
		'method|请求方式'	=>	['length:0,29'],
		'version|接口版本'	=>	['length:0,29'],
		'controller|操作模块'	=>	['length:0,127'],
		'action|操作方法'	=>	['length:0,127'],
		'url|请求的URL'	=>	['length:0,16777214'],
		'params|请求参数'	=>	['length:0,16777214'],
		'title|页面标题'	=>	['length:0,254'],
		'code|响应状态'	=>	['egt:0','number','integer'],
		'result|响应结果'	=>	['length:0,16777214'],
		'sql|执行的sql'	=>	['length:0,16777214'],
		'ip|访问IP'	=>	['length:0,127'],
		'waste_time|接口处理时间'	=>	['length:0,127'],
		'data_create_time|数据时间'	=>	['length:0,127'],

    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [];


    /**
     * 验证场景
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $scene = [
   		'index'	=>	[],
		'save'	=>	['admin_id','admin_name','user_agent','app_name','method','version','controller','action','url','params','title','code','result','sql','ip','waste_time','data_create_time','create_time','update_time'],
		'update'	=>	['id','admin_id','admin_name','user_agent','app_name','method','version','controller','action','url','params','title','code','result','sql','ip','waste_time','data_create_time','create_time','update_time'],
		'read'	=>	['id'],
		'delete'	=>	['id'],
		'changeStatus'	=>	['id'],
		'sortable'	=>	['id'],

    ];
}
