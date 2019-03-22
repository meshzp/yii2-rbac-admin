## IntegReady RBACadmin Module

Installing the RBACadmin module
  
1) Add to `composer.json` following strings:  
```json
"repositories": [
    {
        "type": "vcs",
        "url":  "git@bitbucket.org:se135/yii2-rbac-admin.git"
    }
]
```
  
2) Update composer from console
> composer require  "meshzp/yii2-rbac-admin"
  
3) Add to `console/config/main.php` following strings in order to make module console commands working:  
```php
'modules' => [
    'rbacadmin' => [
        'class' => 'meshzp\rbacadmin\Module',
    ],
],

// Add migration namespace
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationNamespaces' => [
            // ...
            'meshzp\rbacadmin\migrations',
        ],
    ],
],
```

4) Add to `web/config/main.php` following strings:  
```php
'bootstrap'  => [
    'rbacadmin',    // in order to make module bootstrapers working
],
'components' => [
    // in order to change authorization manager to module manager (do not forget to remove existing 'authManager' element)
    'authManager'  => [
        'class' => 'meshzp\rbacadmin\components\rbac\PermManager',
    ],
    // in order to change identity class to module identity class (do not forget to remove existing 'user' element
    'user'         => [
        'identityClass'   => 'meshzp\rbacadmin\models\AdminUser',
        'identityCookie'  => ['name' => '_aidentity', 'httpOnly' => true],
        'enableAutoLogin' => true,
    ],
    // in order to change settings manager to module manager (don not forget to remove existing 'settings' element)
    'settings'     => [
        'class' => 'meshzp\rbacadmin\components\Settings'
    ],
],
'modules'             => [
    // Reassign gridview module to Kartik
    'gridview'   => [
        'class' => '\kartik\grid\Module',
    ],
    // in order to init module. optionally you can fill 3 arrays of default access rules:
    // 'allowToAll' - routes that could be accessed by all (signed & guests) users by default
    // 'allowToLogged' - routes that could be accessed by logged (signed) users by default
    // 'allowToGuest' - routes that could be accessed by guests only by default
    // all rules, described below, used as an example!
    'rbacadmin'   => [
        'class'         => 'meshzp\rbacadmin\Module',
        'allowToAll'    => [
            '/site/index',
        ],
        'allowToLogged' => [
            '/site/logout',
            '/site/#',
        ],
        'allowToGuest'  => [
            '/site/login',
            '/rbacadmin/auth/two-factor',
            '/rbacadmin/auth/two-factor-recovery',
        ],
    ],
],
// in order to apply module access control in all site controllers and avoid AccessControl behaviors in each controller
'as access'  => [
    'class' => 'meshzp\rbacadmin\components\rbac\PermAccessControl',
],
```
5) Add to `web/config/bootstrap.php` following strings. This gives ability to create access-right-based menu (uses module's MenuNav component instead of standard Yii2 Nav component) or to create access-right-based links (uses module's PermHtml component instead of standard Yii2 Html component). Make sure you are using Html component to create links in your project views. :
```php
\Yii::$container->setDefinitions([
    'yii\helpers\Html'       => 'meshzp\rbacadmin\components\PermHtml',
    'yii\bootstrap\Nav'      => 'meshzp\rbacadmin\components\MenuNav',
    'yii\bootstrap\Dropdown' => 'meshzp\rbacadmin\components\MenuDropdown',
]);
```

6) Apply a migration of module from console (make sure there is no errors in console)  
> php yii migrate
  
7) Create default admin user with rights to current module controllers and actions, from console  
> php yii rbacadmin/create/admin **login** **password**
  
8) Use module's LoginForm instead of standard Yii2 LoginForm in site/login action  
```php
$model = new TwoFactorAuthForm(['scenario' => TwoFactorAuthForm::SCENARIO_LOGIN]);
```
instead of
```php
$model = new LoginForm();
```
  
9) Module uses swiftmailer, make sure you have the following strings in your `web/config/main.php` 
```php
'components'    => [
    'mailer'    => [
        'class'            => 'yii\swiftmailer\Mailer',
        'useFileTransport' => false,
    ],
],  
```

----------
  
Every time when you enter Individual Permission Page, module parses all available controllers of your project (also module parses all controllers of all external modules of your project). RBACadmin parses all PHPdocs of all Controllers and Actions and add them into module base.
So when you enter Individual Permission Page, you always will see actual info about your project.

By the way, you can hide some Controllers from parsing:) Just use `@hide_controller` directive in proper Controller's PHPDoc.

If you want to add custom permission, you can simply add it into any Controller's PHPDoc (strongly recommended to add it into proper Controller's PHPDoc, in order to know where custom permission was used) with the following syntaxis:
`@permission '<permission_name>' <permission_info>`

You can use predefined menu-items list of the module by using `MenuNav::RbacManagementMenuItems()` in your menu array;

You can use [[yii\grid\GridView]] ActionColumn integration by using the following syntax: 
`'columns' => [
           ...
           [
               'class' => 'yii\grid\ActionColumn',
               'template' => PermHtml::filterActionColumn(['view','update','delete'])
           ]
       ],`