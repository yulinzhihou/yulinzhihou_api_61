<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class VersionBaseCreate extends Migrator
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
        $table = $this->table('version_base',['primary key'=>'id','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','engine'=>'innodb','comment'=>'游戏版本表']);
        $table
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'游戏版本名'])
            ->addColumn('key','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'游戏版本标识'])
            ->addColumn('desc','string',['limit'=>255,'null'=>false,'default'=>'','comment'=>'游戏版本说明'])
            ->addColumn('link','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'游戏链接'])
            ->addColumn('file','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'版本文件'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'增加时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('key')
            ->create();
    }
}
