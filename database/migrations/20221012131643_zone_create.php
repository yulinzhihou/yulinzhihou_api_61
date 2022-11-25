<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class ZoneCreate extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('zone',['primary key'=>'id','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','engine'=>'innodb','comment'=>'游戏分区表']);
        $table
            ->addColumn('version_id','integer',['limit'=>10,'signed'=>true,'null'=>false,'default'=>0,'comment'=>'版本ID'])
            ->addColumn('type','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'数据库类型'])
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'分区名'])
            ->addColumn('key','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'分区标识符'])
            ->addColumn('host','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'IP地址'])
            ->addColumn('username','string',['limit'=>128,'null'=>false,'default'=>'root','comment'=>'用户名'])
            ->addColumn('password','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'密码'])
            ->addColumn('port','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>3306,'comment'=>'端口'])
            ->addColumn('sort','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'排序'])
            ->addColumn('dbname','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'数据库名'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'状态,0=禁用，1=启用'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'增加时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('key')
            ->addIndex('host')
            ->create();
    }
}
