<?php

namespace app\library;

use Baiy\ThinkAsync\Facade\Async;
use think\facade\Session;

/**
 * 异步处理类，走队列
 */
trait AsyncAction
{
    /**
     * 异步处理成本数据同步方法
     * @param string $data
     * @return string
     */
    public function asyncCost(string $data):string
    {
        // 异步延迟执行 延迟20秒
        Async::delay(20, AsyncAction::class, 'asyncCost2ProductSku',$data);
        return '';
    }


    /**
     * 异步接收成本系统同步成本相关字段的数据
     */
    public static function asyncCost2ProductSku(string $skus):void
    {
        // 进来先判断是否有传数据
        if ($skus != '') {
            // 如果不为空，直接先存一份日志，
            $costData = [
                'admin_id'      => Session::get('user_info')['uc_id']??0,
                'admin_name'    => Session::get('nickname',''),
                'data'          => $skus,
                'status'        => 0,
                'create_time'   => time(),
                'update_time'   => time()
            ];
            $async = (new \app\admin\model\ProductCostAsync())->create($costData);
            $asyncId = $async->id;
            $result = true;
            // 处理更新逻辑
            $costSkuArr = explode(',',$skus);
            if (!empty($costSkuArr)) {
                // 循环查询成本信息
                foreach ($costSkuArr as $cost) {
                    $costData = (new \app\admin\model\Costs())->getCostInfoBySku($cost);
                    // 通过sku查询本库的数据
                    if (!empty($costData)) {
                        $productSku = (new ProductSku())->where('sku',$cost)->save($costData);
                        // 所以有数据更新的时候才会写数据库，没数据更新的时候返回 0
                        $productSku = $productSku == 0 || $productSku;
                        $result = $result && $productSku;
                    }
                }

                if ($result) {
                    // 更新成功需要标记更新状态。
                    (new \app\admin\model\ProductCostAsync())->where('id',$asyncId)->save(['status' => 1]);
                }
            }
        }
    }


    /**
     * 异步处理失败传到ERP的数据
     */
    public static function asyncDoFailData():void
    {
        $failureData = \app\admin\model\ProductAsyncFailure::select()->toArray();
        $erp = new ERP();
        $AccessToken = $erp->AccessToken;
        if (!empty($failureData)) {
            foreach ($failureData as $failureDatum) {
                $erpData = json_decode($failureDatum['data'],true);
                $erpData['ACCESSTOKEN'] = $AccessToken;

                if ($failureDatum['type'] == 'category') {
                    // 处理分类异常的数据
                    $erpReturn = $erp->asyncCategoryToMaterialGroup($erpData);
                    if (isset($erpReturn['Details']) && $erpReturn['Details'][0]['Yid'] > 0) {
                        // 表示成功了
                        \app\admin\model\ProductAsyncFailure::where('id',$failureDatum['id'])->delete();
                        // 写入日志
                        $newData = [
                            'admin_id'      => Session::get('user_info')['uc_id'],
                            'admin_name'    => Session::get('user_info')['nickname'],
                            'type'          => 'category',
                            'before_data'   => json_encode([]),
                            'data'          => $failureDatum['data'],
                            'status'        => 1
                        ];
                        ProductAsyncLog::create($newData);
                    }

                } elseif ($failureDatum['type'] == 'product_sku') {
                    // 处理SKU 异常的数据
                    $erpReturn = $erp->asyncSkuToMaterial($erpData);
                    if (isset($erpReturn['Details']) && $erpReturn['Details'][0]['Yid'] > 0) {
                        // 表示成功了
                        \app\admin\model\ProductAsyncFailure::where('id',$failureDatum['id'])->delete();
                        // 写入日志
                        // 写入日志
                        $newData = [
                            'admin_id'      => Session::get('user_info')['uc_id'],
                            'admin_name'    => Session::get('nickname'),
                            'type'          => 'product',
                            'before_data'   => json_encode([]),
                            'data'          => $failureDatum['data'],
                            'status'        => 1
                        ];
                        ProductAsyncLog::create($newData);
                    }
                }

            }
        }
    }

}