<?php
declare(strict_types=1);

namespace  app\library;

/**
 * 高级数组处理类
 */
trait YArray
{
    /**
     * 获取数组层级，确定是几维数组
     * @return int
     */
    public function getArrayLevel():int
    {
        return 2;
    }

    /**
     * 通过键名取值
     * @param array $data
     * @param string $key
     * @return string
     */
    public function getValueByKey(array $data,string $key):string
    {
        return '';
    }


    /**
     * 通过键名取一组值
     * @param array $data
     * @param string $key
     * @return array
     */
    public function getValuesByKey(array $data,string $key):array
    {
        return [];
    }

    /**
     * 通过值取键名
     * @param array $data
     * @param string $value
     * @return string
     */
    public function getKeyByValue(array $data,string $value):string
    {
        return '';
    }

    /**
     * 根据二维数据某个字段进行二维数组的排序
     *
     * @param array $data  需要进行字段排序的二维数组
     * @param string $field 二维数组排序字段名
     * @param int $sort    排序规则，SORT_DESC=倒序，SORT_AES=升序
     * @return array       返回排序后的数组
     */
    public function sortArrayByFieldValue(array $data, string $field, int $sort = SORT_DESC) : array
    {
        array_multisort(array_column($data,$field),$sort,$data);
        return $data;
    }

}