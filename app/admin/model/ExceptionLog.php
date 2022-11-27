<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 * 异常日志模型
 */
class ExceptionLog extends Base
{
     protected $schema = [
		'id'	        =>	'int',
		'admin_id'	    =>	'int',
		'admin_name'	=>	'varchar',
		'app_name'	    =>	'varchar',
		'url'	        =>	'mediumtext',
		'ip'	        =>	'varchar',
		'user_agent'	=>	'varchar',
		'params'	    =>	'mediumtext',
		'class'	        =>	'varchar',
		'action'	    =>	'varchar',
		'type'	        =>	'varchar',
		'error_file'	    =>	'varchar',
		'error_line'	=>	'int',
		'message'	    =>	'mediumtext',
		'sql'	        =>	'mediumtext',
		'data_create_time'	=>	'datetime',
		'create_time'	=>	'int',
		'update_time'	=>	'int',
    ];

    /**
     * 构建数据
     */
    public static function buildExceptionData(array $data):void
    {
        self::create($data);
    }
}
