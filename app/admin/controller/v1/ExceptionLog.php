<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\Controller\Base;
use app\admin\model\ExceptionLog as ExceptionLogModel;
use app\admin\validate\ExceptionLog as ExceptionLogValidate;

/**
 * ExceptionLog
 */
class ExceptionLog extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new ExceptionLogModel();
        $this->validate = new ExceptionLogValidate();

        // 请求接口的字段过滤，
        $this->inputField = [];

        // 新增页面字段，默认为对应数据表全部字段，包括验证规则
        $this->addField = [];

        // 列表显示字段，默认为对应数据表全部字段，包括验证规则
        $this->indexField = [];

        // 编辑页字段，默认为对应数据表全部字段，包括验证规则
        $this->editField = [];

        // 详情接口字段，默认为对应数据表全部字段，包括验证规则, 可以指定详情接口只返回某些字段
        $this->infoField = [];

        // 额外增加请求接口字段数据，如：需要额外增加数据在请求接口里面请使用 [ 'user_price' => 999.99 ]
        $this->params = [];

        /**
         * 编辑接口忽略字段，不允许提交过来的字段
         */
        $this->editExpectField = ['create_time','update_time'];

        /**
         * 新增接口忽略字段，不允许提交过来的字段
         */
        $this->addExpectField = ['create_time','update_time'];

        // 快速搜索，匹配多个字段

        //if (isVarExists($this->inputData,'quick_search')) {
        //    $this->vague = array_merge($this->vague, array_fill_keys(['name','value','remark'],$this->inputData['quick_search']));
        //}

        // 删除方法检测数据有没有被引用,先查看有没有下级分类，再查当前角色有没有被其他用户引用

        //if ($this->request->method() == 'DELETE') {
        //    if (isVarExists($this->inputData,'id')) {
        //
        //    }
        //}
    }

}
