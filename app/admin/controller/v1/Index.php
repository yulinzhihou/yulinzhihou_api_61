<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\Controller\Base;
use app\admin\model\ExceptionLog;
use app\admin\model\Index as IndexModel;

/**
 * Index
 */
class Index extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new IndexModel();
    }

    /**
     * 显示资源列表
     */
    public function index() :\think\Response\Json
    {
        return $this->jr('测试消息');
    }

}
