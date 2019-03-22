<?php
namespace meshzp\rbacadmin\migrations;

use yii\db\Migration;

/**
 * Class m180130_135911_change_nickname_username
 */
class m180130_135911_change_nickname_username extends Migration
{

    public $tables = [
        'userTable'              => '{{%perm_users}}',
        'userRequestLogTable'    => '{{%perm_request_log}}',
    ];

    /**
     * Changes Nickname -> Username
     */
    public function up()
    {
        $this->renameColumn($this->tables['userTable'], 'nickname', 'username');
        $this->renameColumn($this->tables['userRequestLogTable'], 'nickname', 'username');
        $this->alterColumn($this->tables['userTable'], 'mobile', $this->string(64)->null());
    }

    /**
     * Revert Username -> Nickname
     * @return bool
     */
    public function down()
    {
        $this->renameColumn($this->tables['userTable'], 'username', 'nickname');
        $this->renameColumn($this->tables['userRequestLogTable'], 'username', 'nickname');
        $this->alterColumn($this->tables['userTable'], 'mobile', $this->string(64));
        return true;
    }
}
