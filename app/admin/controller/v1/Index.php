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

//    /**
//     * 显示资源列表
//     */
//    public function index() :\think\Response\Json
//    {
//        try {
//            if (!empty($this->params)) {
//                $this->inputData = array_merge($this->inputData,$this->params);
//            }
//            //判断是否需要分页
//            if (isset($this->inputData['page']) && $this->inputData['page'] != 0) {
//                $this->page = (int)$this->inputData['page'];
//            }
//
//            if (isset($this->inputData['size']) && $this->inputData['size'] != 0) {
//                $this->size = (int)$this->inputData['size'];
//            }
//            // 列表输出字段
//            if (isset($this->indexField) && !empty($this->indexField)) {
//                $this->field = $this->indexField;
//            }
//
//            $result = $this->model->getIndexList($this->page,$this->size,$this->field,$this->vague,$this->focus,$this->order);
//
//            $this->sql = $this->model->getLastSql();
//            //构建返回数据结构
//            return $this->jr('获取成功',true);
//        } catch (\Exception $e) {
//            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'controller',$this->sql,$this->adminInfo);
//            return $this->jr('详情数据异常，请查看异常日志或者日志文件进行修复');
//        }
//
//    }

}
