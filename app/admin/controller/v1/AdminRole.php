<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\Controller\Base;
use app\admin\model\AdminRole as AdminRoleModel;
use app\admin\validate\AdminRole as AdminRoleValidate;

/**
 * AdminRole
 */
class AdminRole extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminRoleModel();
        $this->validate = new AdminRoleValidate();
    }

}
