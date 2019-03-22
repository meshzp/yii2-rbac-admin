<?php

namespace meshzp\rbacadmin\migrations;

use yii\db\Migration;

/**
 * RBACAdmin Module Migration
 */
class m161206_110945_perm extends Migration
{
    public $tables = [
        'userTable'              => '{{%perm_users}}',
        'authItemTable'          => '{{%perm_auth_item}}',
        'authItemRelationsTable' => '{{%perm_auth_item_relations}}',
        'userSessionsTable'      => '{{%perm_user_sessions}}',
        'userRequestLogTable'    => '{{%perm_request_log}}',
        'userSettingsTable'      => '{{%perm_users_settings}}',
        'userLoginLogTable'      => '{{%perm_users_login_log}}',
        'settingsTable'          => '{{%perm_settings}}',
    ];

    /**
     * @inheritdoc
     */
    public function up()
    {
        $collission   = 1;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->schema->getTableSchema($this->tables['userTable'], true) === null) {
            $this->createTable($this->tables['userTable'], [
                'id'                   => $this->primaryKey(),
                'nickname'             => $this->char(18)->unique()->notNull(),
                'password_hash'        => $this->char(128)->notNull(),
                'password_reset_token' => $this->char(64),
                'auth_key'             => $this->char(32)->notNull(),
                'status'               => 'tinyint(4) NOT NULL',
                'mobile'               => $this->char(16)->notNull(),
                'email'                => $this->char(64)->unique()->notNull(),
                'auth_type'            => 'tinyint(4) NOT NULL DEFAULT 1',
                'change_pass_date'     => $this->dateTime(),
                'group_flag'           => $this->integer(11)->notNull()->defaultValue(0),
                'name'                 => $this->string(45),
                'surname'              => $this->string(45),
                'patronymic'           => $this->string(45),
                'sex'                  => $this->integer(11),
                'birth_date'           => $this->dateTime(),
                'start_date'           => $this->dateTime(),
                'company_position'     => $this->string(45),
                'in_group'             => $this->integer(11)->notNull()->defaultValue(0),
                'description'          => $this->string(256),
                'sip'                  => $this->char(16),
                'can_get_child_info'   => $this->integer(11)->notNull()->defaultValue(0),
                'group_head_id'        => $this->integer(11)->notNull()->defaultValue(0),
                'date_created'         => $this->dateTime()->notNull(),
                'date_updated'         => $this->dateTime()->notNull(),
            ], $tableOptions);
            $collission = 0;
        }

        if ($this->db->schema->getTableSchema($this->tables['authItemTable'], true) === null) {
            $this->createTable($this->tables['authItemTable'], [
                'name'        => 'char(64) NOT NULL PRIMARY KEY',
                'type'        => $this->integer(11)->notNull(),
                'description' => $this->text(),
                'rule_name'   => $this->string(64),
                'data'        => $this->text(),
                'controller'  => $this->char(64)->notNull(),
                'created_at'  => $this->dateTime()->notNull(),
                'updated_at'  => $this->dateTime()->notNull(),
            ], $tableOptions);
        }

        if ($this->db->schema->getTableSchema($this->tables['authItemRelationsTable'], true) === null) {
            $this->createTable($this->tables['authItemRelationsTable'], [
                'name'     => $this->char(64)->notNull(),
                'admin_id' => $this->integer(11)->notNull(),
                'enabled'  => 'tinyint(4) NOT NULL',
            ], $tableOptions);
        }

        if ($this->db->schema->getTableSchema($this->tables['userSessionsTable'], true) === null) {
            $this->createTable($this->tables['userSessionsTable'], [
                'id'         => $this->primaryKey(),
                'user_id'    => $this->integer(11)->notNull(),
                'session_id' => $this->string(45)->notNull(),
            ], $tableOptions);
        }

        if ($this->db->schema->getTableSchema($this->tables['userRequestLogTable'], true) === null) {
            $this->createTable($this->tables['userRequestLogTable'], [
                'id'           => $this->primaryKey(),
                'user_id'      => $this->integer(11)->notNull(),
                'nickname'     => $this->char(64)->notNull(),
                'request'      => $this->string(255)->notNull(),
                'get_params'   => $this->text()->notNull(),
                'post_params'  => $this->text()->notNull(),
                'date_created' => $this->dateTime()->notNull(),
            ], $tableOptions);
        }

        if ($this->db->schema->getTableSchema($this->tables['userSettingsTable'], true) === null) {
            $this->createTable($this->tables['userSettingsTable'], [
                'user_id'                       => $this->primaryKey(),
                'user_type'                     => $this->integer(11)->notNull()->defaultValue(0),
                'security_recovery_codes_alert' => 'tinyint(4) NOT NULL DEFAULT 1',
                'security_secret_code'          => $this->char(16)->notNull(),
                'security_sms_fallback_number'  => $this->char(16)->defaultValue(null),
            ], $tableOptions);
        }

        if ($this->db->schema->getTableSchema($this->tables['userLoginLogTable'], true) === null) {
            $this->createTable($this->tables['userLoginLogTable'], [
                'id'             => $this->primaryKey(),
                'user_id'        => $this->integer(11)->notNull(),
                'ip'             => $this->char(16)->notNull(),
                'date_attempted' => $this->dateTime()->notNull(),
                'login_type'     => 'tinyint(4) NOT NULL',
                'is_successful'  => 'tinyint(4) NOT NULL',
            ], $tableOptions);
        }

        if ($this->db->schema->getTableSchema($this->tables['settingsTable'], true) === null) {
            $this->createTable($this->tables['settingsTable'], [
                'id'       => $this->primaryKey(),
                'type'     => $this->string(255)->notNull(),
                'section'  => $this->string(255)->notNull(),
                'key'      => $this->string(255)->notNull(),
                'value'    => $this->text(),
                'active'   => $this->boolean(),
                'created'  => $this->dateTime(),
                'modified' => $this->dateTime(),
            ], $tableOptions);
            $this->insert($this->tables['settingsTable'], [
                'id'      => '3',
                'type'    => 'boolean',
                'section' => 'login',
                'key'     => 'limit_authorize_for_single_ip',
                'value'   => '0',
                'active'  => '1',
            ]);
            $this->insert($this->tables['settingsTable'], [
                'id'      => '4',
                'type'    => 'boolean',
                'section' => 'login',
                'key'     => 'user_must_change_pass_on_first_login',
                'value'   => '1',
                'active'  => '0',
            ]);
            $this->insert($this->tables['settingsTable'], [
                'id'      => '5',
                'type'    => 'integer',
                'section' => 'login',
                'key'     => 'password_expiration_time',
                'value'   => '30',
                'active'  => '0',
            ]);
        }

        //Auth Item Relations Table Service
        if (!$collission) {
            $this->addPrimaryKey('name_admin_id', $this->tables['authItemRelationsTable'], ['admin_id', 'name']);
            $this->createIndex('name_idx', $this->tables['authItemRelationsTable'], 'name');
            $this->addForeignKey('name', $this->tables['authItemRelationsTable'], 'name', $this->tables['authItemTable'], 'name', 'CASCADE', 'NO ACTION');
            $this->addForeignKey('user_id_in_log', $this->tables['userLoginLogTable'], 'user_id', $this->tables['userTable'], 'id');
            $this->addForeignKey('user_whos_settings', $this->tables['userSettingsTable'], 'user_id', $this->tables['userTable'], 'id');
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute("SET foreign_key_checks = 0;");
        foreach ($this->tables as $table) {
            if ($this->db->schema->getTableSchema($table, true) !== null) {
                $this->dropTable($table);
            }
        }
        $this->execute("SET foreign_key_checks = 1;");
    }
}
