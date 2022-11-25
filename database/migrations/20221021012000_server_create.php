<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class ServerCreate extends Migrator
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
        $table = $this->table('server',['primary key'=>'id','auto_increment'=>true,'charset'=>'utf8mb4','collation'=>'utf8mb4_general_ci','engine'=>'innodb','comment'=>'开服配置表']);
        $table
            ->addColumn('name','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'开区名称'])
            ->addColumn('admin_id','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'版本ID'])
            ->addColumn('version_id','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'版本ID'])
            ->addColumn('type','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'开服类型0=两库同机,1=两库不同机'])

            ->addColumn('web_host','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'web库地址'])
            ->addColumn('web_user','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'web库用户名'])
            ->addColumn('web_pass','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'web库密码'])
            ->addColumn('web_port','integer',['limit'=>MysqlAdapter::INT_SMALL,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'web库端口'])

            ->addColumn('game_host','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'game库地址'])
            ->addColumn('game_user','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'game库用户名'])
            ->addColumn('game_pass','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'game库密码'])
            ->addColumn('game_port','integer',['limit'=>MysqlAdapter::INT_SMALL,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'game库端口'])

            ->addColumn('online_key','string',['limit'=>128,'null'=>false,'default'=>'','comment'=>'在线GM通讯密钥'])

            ->addColumn('status','integer',['limit'=>MysqlAdapter::INT_TINY,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'状态:0=禁用,1=启用'])
            ->addColumn('test_web_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'测试web库时间'])
            ->addColumn('test_game_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'测试game库时间'])
            ->addColumn('create_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'创建时间'])
            ->addColumn('update_time','integer',['limit'=>10,'signed'=>false,'null'=>false,'default'=>0,'comment'=>'更新时间'])
            ->addIndex('id')
            ->addIndex('name')
            ->addIndex('admin_id')
            ->addIndex('version_id')
            ->create();
    }
}
