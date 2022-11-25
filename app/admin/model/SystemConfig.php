<?php
declare (strict_types = 1);

namespace app\admin\model;

use app\admin\model\Base;
use think\facade\Log;

/**
 * @mixin \think\Model
 */
class SystemConfig extends Base
{
    protected $schema = [
        'id'	=>	 'int',
        'name'	=>	 'string',
        'group'	=>	 'string',
        'title'	=>	 'string',
        'tip'	=>	 'string',
        'type'	=>	 'string',
        'value'	=>	 'string',
        'content'	=>	 'string',
        'rule'	    =>	 'string',
        'extend'	=>	 'string',
        'allow_del'	=>	 'int',
        'weigh'	    =>	 'int',
        'create_time'	=>	 'int',
        'update_time'	=>	 'int',
    ];

    /**
     * 查看列表数据
     * @param int $page 分页页码
     * @param int $pageSize 分页类目数量
     * @param array $field 需要输入的字段，默认为全部输出，筛选表字段
     * @param array $vague 需要进行模糊查询的字段，如： a like %a%
     * @param array $focus 精确搜索条件，如a=1,b=2这种精确等于某个值的
     * @param array $order 排序字段，如：['id'=>'desc','sort'=>'desc']
     * @param array $range 区间字段，如：['create_time'=>['2022','2023']]
     * @return array
     */
    public function getIndexList(int $page,int $pageSize,array $field = [], array $vague = [],array $focus = [],array $order = [],array $range = []):array
    {
        try {
            //不需要分页
            $resultData = $this->select()->toArray();
            // 取ID为1的数据
            $config = json_decode($resultData[0]['value'],true);
            $keyFields = array_column($config,'key');
            $configGroup = array_column($config,'value','key');
            // 取快捷访问入口
            $quickEntrance = [];

            $newArr = [];
            foreach ($keyFields as $keyField) {
                $tmp = [
                    'list' => [],
                    'name' => $keyField,
                    'title'=> $configGroup[$keyField],
                ];
                foreach ($resultData as $data) {
                    if ($data['type'] == 'array') {
                        $data['value'] = json_decode($data['value'],true);
                    }
                    if ($keyField == $data['group']) {
                        $tmp['list'][] = $data;
                    }
                    if ($data['name'] == 'config_quick_entrance') {
                        $quickEntrance = $data['value'];
                    }
                }
                $newArr[$keyField] = $tmp;
            }
            return ['list'=>$newArr,'remark'=>'','configGroup'=>$configGroup,'quickEntrance' => $quickEntrance];

        } catch (\Exception $e) {
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return [];
        }

    }



    /**
     * 新增数据
     * @param array $data
     * @return bool
     */
    public function addData(array $data) : bool
    {
        try {
            $this->startTrans();
            if (isVarExists($data,'type')) {
                if (isVarExists($data,'rule')) {
                    $data['rule'] = json_encode($data['rule']);
                }
                $result = parent::addData($data);
                if ($result) {
                    $this->commit();
                    return true;
                } else {
                    $this->rollback();
                    return false;
                }

            } else {
                // 提取基础配置分组数据，
                $result1 = $this->where('name','config_group')->find()->save(['value'=>json_encode($data['config_group'])]);

                $result2 = $this->where('name','config_quick_entrance')->find()->save(['value'=>json_encode($data['config_quick_entrance'])]);

                unset($data['config_group']);
                unset($data['config_quick_entrance']);
                $result = $result3 = true;
                foreach ($data as $key => $item) {
                    $result4 = $this->where('name',$key)->find()->save(['value'=>$item]);
                    $result = $result3 && $result4;
                }
                if ($result1 && $result2 && $result) {
                    $this->commit();
                    return true;
                } else {
                    $this->rollback();
                    return false;
                }
            }

        } catch (\Exception $e) {
            $this->rollback();
            ExceptionLog::buildExceptionData($e,__LINE__,__FILE__,__CLASS__,__FUNCTION__,'model',$this->getLastSql());
            Log::sql($e->getMessage());
            return false;
        }
    }


}
