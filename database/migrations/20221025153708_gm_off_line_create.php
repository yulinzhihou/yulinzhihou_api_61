<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class GmOffLineCreate extends Migrator
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
        $table = $this->table('gm_offline',['primary key'=>'id','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','engine'=>'innodb','comment'=>'离线GM配置表']);
        $table
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'标识名'])
            ->addColumn('server_id','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'开服ID'])
            ->addColumn('admin_id','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'版本ID'])
            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'状态:0=禁用,1=启用'])
            ->addColumn('build_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'构建时间'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('admin_id')
            ->addIndex('server_id')
            ->create();
    }
}
