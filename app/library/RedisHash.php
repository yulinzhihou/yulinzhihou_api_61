<?php
declare(strict_types=1);
namespace app\library;


use think\cache\driver\Redis;

/**
 * Redis hash类
 * Class RedisHash
 * @package app\base\controller
 */
class RedisHash extends Redis
{
    public function __construct($options = [])
    {
        parent::__construct($options);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $key 默认值
     * @throws \RedisException
     */
    public function hget($name, $key)
    {
        return $this->handler->hGet($name,$key);
    }


    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param array $key 键名，1 2 3 4 5
     * @throws \RedisException
     */
    public function hmget($name, $key)
    {
        return $this->handler->hMGet($name,$key);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @throws \RedisException
     */
    public function hgetall(string $name)
    {
        return $this->handler->hGetAll($name);
    }


    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @return boolean
     * @throws \RedisException
     */
    public function hset(string $name, $value)
    {
        return $this->handler->hSet($name, $value);
    }


    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     */
    public function hmset($name, $value, $expire = null)
    {
        return $this->handler->hMSet($name, $value);
    }


}