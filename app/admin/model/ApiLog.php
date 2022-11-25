<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;

/**
 * @mixin \think\Model
 */
class ApiLog extends Base
{
     protected $schema = [
		'id'	=>	'int',
		'admin_id'	=>	'int',
		'admin_name'	=>	'varchar',
		'user_agent'	=>	'varchar',
		'app_name'	=>	'varchar',
		'method'	=>	'varchar',
		'version'	=>	'varchar',
		'controller'	=>	'varchar',
		'action'	=>	'varchar',
		'url'	=>	'mediumtext',
		'params'	=>	'mediumtext',
		'title'	=>	'varchar',
		'code'	=>	'int',
		'result'	=>	'mediumtext',
		'sql'	=>	'mediumtext',
		'ip'	=>	'varchar',
		'waste_time'	=>	'decimal',
		'data_create_time'	=>	'datetime',
		'create_time'	=>	'int',
		'update_time'	=>	'int',

    ];
}
