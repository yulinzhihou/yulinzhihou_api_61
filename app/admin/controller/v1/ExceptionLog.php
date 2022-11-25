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
    }

}
