<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class VersionFilesConfigCreate extends Migrator
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
        $table = $this->table('version_files_config',['primary key'=>'id','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','engine'=>'innodb','comment'=>'游戏文件表']);
        $table
            ->addColumn('version_id','integer',['limit'=>10,'signed'=>true,'null'=>false,'default'=>0,'comment'=>'版本ID'])
            ->addColumn('file_type','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'游戏文件类型,item|gem|pet|monster|equip'])
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'文件名'])
            ->addColumn('path','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'文件路径'])
            ->addColumn('hash','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'文件hash'])
            ->addColumn('file_hash','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'当前同步文件hash'])
            ->addColumn('async_status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'同步状态,0=未同步,1=已同步'])
            ->addColumn('async_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'同步时间'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->addIndex('id')
            ->addIndex('file_type')
            ->addIndex('name')
            ->create();
    }
}
