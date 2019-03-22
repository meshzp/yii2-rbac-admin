<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace meshzp\rbacadmin\components\rbac;

use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\rbac\DbManager;
use yii\caching\TagDependency;

/**
 * DbManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by [[db]]. The database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/rbac/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the names of the tables used to store the authorization and rule data by setting [[itemTable]],
 * [[itemChildTable]], [[assignmentTable]] and [[ruleTable]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class PermManager extends DbManager
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbManager object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';

    /**
     * @var string the name of the table storing authorization items. Defaults to "auth_item".
     */
    public $itemTable = '{{%perm_auth_item}}';

    public $adminUsersTable = '{{%perm_users}}';

    public $authItemRelationsTable = '{{%perm_auth_item_relations}}';

    public $users;

    public $groups;

    /**
     * @var Cache|array|string the cache used to improve RBAC performance. This can be one of the following:
     *
     * - an application component ID (e.g. `cache`)
     * - a configuration array
     * - a [[\yii\caching\Cache]] object
     *
     * When this is not set, it means caching is not enabled.
     *
     * Note that by enabling RBAC cache, all auth items, rules and auth item parent-child relationships will
     * be cached and loaded into memory. This will improve the performance of RBAC permission check. However,
     * it does require extra memory and as a result may not be appropriate if your RBAC system contains too many
     * auth items. You should seek other RBAC implementations (e.g. RBAC based on Redis storage) in this case.
     *
     * Also note that if you modify RBAC items, rules or parent-child relationships from outside of this component,
     * you have to manually call [[invalidateCache()]] to ensure data consistency.
     *
     * @since 2.0.3
     */
    public $cache;
    /**
     * @var string the key used to store RBAC data in cache
     * @see cache
     * @since 2.0.3
     */
    public $cacheKey = 'rbac';

    /**
     * @var Item[] all auth items (name => Item)
     */
    protected $items;


    /**
     * Initializes the application component.
     * This method overrides the parent implementation by establishing the database connection.
     *
     * @throws InvalidConfigException if something goes wrong
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
    }

    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        if (!isset($userId)) {
            $userId = 0;
        }
        // Проверяем правила по умолчанию
        /** @var \meshzp\rbacadmin\Module $module */
        $module = Yii::$app->getModule('rbacadmin');
        if (in_array($permissionName, $module->allowToAll) || (!empty($userId) && in_array($permissionName, $module->allowToLogged)) || empty($userId) && in_array($permissionName, $module->allowToGuest)) {
            return true;
        }

        $query = Yii::$app->db->cache(function () use ($permissionName, $userId) {
            return (new Query())
                ->select([
                    'coalesce(cairu.enabled, cairg.enabled, caircu.enabled, caircg.enabled, 0) as enabled'
                ])
                ->from($this->adminUsersTable . ' AS au')
                ->leftJoin($this->itemTable . ' AS cai', 'cai.name = "' . $permissionName . '"')
                ->leftJoin($this->authItemRelationsTable . ' AS cairu', 'cairu.name = cai.name and cairu.admin_id = au.id')
                ->leftJoin($this->authItemRelationsTable . ' AS cairg', 'cairg.name = cai.name and cairg.admin_id = au.in_group and au.group_flag = ' . AdminUser::GROUP_FLAG_IT_IS_USER)
                ->leftJoin($this->itemTable . ' AS caic', 'caic.controller = substring_index("' . $permissionName . '", concat("/", substring_index("' . $permissionName . '", "/", -1)), 1) and caic.type = 1')
                ->leftJoin($this->authItemRelationsTable . ' AS caircu', 'caircu.name = caic.name and caircu.admin_id = au.id')
                ->leftJoin($this->authItemRelationsTable . ' AS caircg', 'caircg.name = caic.name and caircg.admin_id = au.in_group and au.group_flag = ' . AdminUser::GROUP_FLAG_IT_IS_USER)
                ->where('au.id = ' . $userId)
                ->one();
        }, $module->cache_duration, new TagDependency(['tags'=>[Module::CACHE_TAG]]));
        $grant = $query['enabled'];
        if ($grant) {
            //Если пермишн разрешён по запросу - разрешаем
            return $grant;
        }
        return false;
    }

    /**
     * For yii2-debug
     * @param int|string $userId
     *
     * @return array
     */
    public function getRolesByUser($userId)
    {
        $info = [
            'name' => 'There are no roles available in this version of permission manager',
            'description' => '',
            'ruleName' => '',
            'data' => '',
            'createdAt:datetime' => '' ,
            'updatedAt:datetime' => '',
        ];

        return [$info];
    }

    /**
     * For yii2-debug
     * @param int|string $userId
     *
     * @return array
     */
    public function getPermissionsByUser($userId)
    {
        $info = [
            'name' => 'The list of permissions available in Control/Users section (' . Yii::$app->urlManager->createAbsoluteUrl(['rbacadmin/control/ind-permissions', 'id' => $userId]) . ')',
            'description' => '',
            'ruleName' => '',
            'data' => '',
            'createdAt:datetime' => '' ,
            'updatedAt:datetime' => '',
        ];
        return [$info];
    }
}
