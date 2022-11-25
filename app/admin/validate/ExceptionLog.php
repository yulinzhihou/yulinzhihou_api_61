<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class ExceptionLog extends Validate
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
		'app_name|应用名称'	=>	['length:0,127'],
		'url|请求的URL'	=>	['length:0,16777214'],
		'ip|访问IP'	=>	['length:0,127'],
		'user_agent|请求代理'	=>	['length:0,254'],
		'params|请求参数'	=>	['length:0,16777214'],
		'class|异常模块'	=>	['length:0,127'],
		'action|异常方法'	=>	['length:0,127'],
		'type|文件类型,controller,model,validate,service,common等文件类型'	=>	['length:0,127'],
		'error_file|报错文件'	=>	['length:0,254'],
		'error_line|报错行号'	=>	['egt:0','number','integer'],
		'message|异常信息'	=>	['length:0,16777214'],
		'sql|执行的sql'	=>	['length:0,16777214'],
		'data_create_time|数据时间'	=>	['length:0,16777214'],

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
		'save'	=>	['admin_id','admin_name','app_name','url','ip','user_agent','params','class','action','type','error_file','error_line','message','sql','data_create_time','create_time','update_time'],
		'update'	=>	['id','admin_id','admin_name','app_name','url','ip','user_agent','params','class','action','type','error_file','error_line','message','sql','data_create_time','create_time','update_time'],
		'read'	=>	['id'],
		'delete'	=>	['id'],
		'changeStatus'	=>	['id'],
		'sortable'	=>	['id'],

    ];
}
