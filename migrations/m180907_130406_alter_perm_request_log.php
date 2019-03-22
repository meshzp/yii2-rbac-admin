<?php
namespace meshzp\rbacadmin\migrations;

use yii\db\Migration;

/**
 * Class m180907_130406_alter_perm_request_log
 */
class m180907_130406_alter_perm_request_log extends Migration
{

    public $tables = [
        'userRequestLogTable'    => '{{%perm_request_log}}',
    ];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn($this->tables['userRequestLogTable'], 'request', $this->text()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->alterColumn($this->tables['userRequestLogTable'], 'request', $this->string(255)->notNull());
    }
}
