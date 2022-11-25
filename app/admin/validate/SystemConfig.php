<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class SystemConfig extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id|ID'	    =>	 ['gt:0'],
        'name|变量名'	    =>	 ['length:0,255'],
        'group|分组'	    =>	 ['length:0,255'],
        'title|变量标题'	    =>	 ['length:0,255'],
        'tip|变量描述'	    =>	 ['length:0,255'],
        'type|变量类型'	    =>	 ['length:0,255'],
        'value|变量值'	    =>	 ['length:0,255'],
        'content|字典数据'	=>	 ['length:0,255'],
        'rule|验证规则'	    =>	 ['length:0,255'],
        'extend|拓展规则'	=>	 ['length:0,255'],
        'allow_del|允许删除:0=否,1=是'	=>	 ['egt:0','in:0,1'],
        'weigh|权重'	    =>	 ['egt:0']
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
        'index' => [],
        'read' => ['id'],
        'save' => ['name','group','title','tip','type','value','content','rule','extend','allow_del','weigh'],
        'update' => ['id','name','group','title','tip','type','value','content','rule','extend','allow_del','weigh'],
        'delete' => ['id'],
    ];
}
