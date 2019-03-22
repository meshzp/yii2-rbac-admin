Установка модуля meshzp/rbacadmin

1) Добавить в composer.json

"repositories": [
        {
            "type": "vcs",
            "url":  "git@bitbucket.org:minister87/yii2-rbac-admin.git"
        }
    ],

"require": {
         "meshzp/yii2-rbac-admin": "dev-master"
        }


2) composer update

3) Модуль использует mailer, поэтому нужно чтобы в main.php было следующее:


4) Выполнить в консоли команды:

php yii rbacadmin/create/migration
php yii rbacadmin/create/admin <login> <password>


5) Добавить в main.php

'components'          => [
        'mailer'       => [
            'class'            => 'yii\swiftmailer\Mailer',
            'viewPath'         => '@backend/mail',
            'useFileTransport' => false,
        ],
        ],

'bootstrap'           => [
        'rbacadmin'
    ],

 'modules'             => [
        'rbacadmin'           => [
            'class' => 'meshzp\rbacadmin\Module',
        ],
        ],

  'components'          => [
          'user'         => [
              'identityClass'   => 'meshzp\rbacadmin\models\AdminUser',
              'identityCookie'  => ['name' => '_aidentity', 'httpOnly' => true],
              'enableAutoLogin' => true,
          ],
          'authManager'  => [
                      'class' => 'meshzp\rbacadmin\components\rbac\PermManager',
                  ],
          'settings'     => [
                      'class' => 'meshzp\rbacadmin\components\Settings'
                  ],
          ],

