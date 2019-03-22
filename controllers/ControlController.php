<?php

namespace meshzp\rbacadmin\controllers;

use meshzp\rbacadmin\components\ControlPermission;
use meshzp\rbacadmin\components\rbac\PermAccessControl as AccessControl;
use meshzp\rbacadmin\models\AdminUser;
use meshzp\rbacadmin\models\AdminUserSearch;
use meshzp\rbacadmin\models\ControlPermissions;
use meshzp\rbacadmin\models\ControlPermissionsSearch;
use meshzp\rbacadmin\models\ControlUserSessions;
use meshzp\rbacadmin\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\Controller;
use yii\caching\TagDependency;

/**
 * Control Controller of RBACADMIN module (performs all control actions of module)
 *
 * @property Module $module
 */
class ControlController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
            ],
        ];
    }

    /**
     * Action to show User-Manager Page
     * @return string User-Manager Page
     */
    public function actionUserManager()
    {
        $dataProvider = new ActiveDataProvider([
            'query'      => AdminUser::find()->where(['group_flag' => AdminUser::GROUP_FLAG_IT_IS_USER]),
            'pagination' => false,
            'sort'       => false,
        ]);

        $userModel = new AdminUser();
        $id        = Yii::$app->request->get('id');
        if (!empty($id)) {
            $userModel = AdminUser::findOne(['id' => $id, 'group_flag' => AdminUser::GROUP_FLAG_IT_IS_USER]);
        }

        if (Yii::$app->request->isPost && $userModel->load(Yii::$app->request->post())) {
            $userModel->save();
        }

        return $this->render('user_manager', [
            'dataProvider' => $dataProvider,
            'userModel'    => $userModel,
        ]);
    }

    /**
     * Action to show Groups Page (manage access groups)
     * @return string Groups Page
     */
    public function actionGroups()
    {
        $searchModel  = new AdminUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), AdminUser::GROUP_FLAG_IT_IS_GROUP);

        return $this->render('groups', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Action to activate group at Group Page
     *
     * @param $id integer group_id to activate
     */
    public function actionGroupActivate($id)
    {
        $group = AdminUser::findOne($id);
        if (!empty($group)) {
            $group->scenario = AdminUser::SCENARIO_STATUS_MODERATION;
            $group->status   = AdminUser::STATUS_ACTIVE;
            if ($group->save()) {
                Yii::$app->session->setFlash('success', Yii::t('perm', 'perm-status-activated'));
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('perm', 'perm-word-error'));
            }
        }
    }

    /**
     * Action to ban group at Group Page
     *
     * @param $id integer group_id to ban
     */
    public function actionGroupBan($id)
    {
        $group = AdminUser::findOne($id);
        if (!empty($group)) {
            $group->scenario = AdminUser::SCENARIO_STATUS_MODERATION;
            $group->status   = AdminUser::STATUS_BANNED;
            if ($group->save()) {
                $aUsers = $group->getUsersInGroupIdsArray();
                Yii::$app->session->setFlash('error', var_dump($aUsers));
                if ($aUsers) {
                    $userSessions = ControlUserSessions::find()->andWhere(['in', 'user_id', $aUsers])->all();
                    foreach ($userSessions as $userSession) {
                        try {
                            $userSession->delete();
                        } catch (\Throwable $e) {
                            Yii::$app->session->setFlash('error', $e->getMessage());
                        };
                    }
                };
                Yii::$app->session->setFlash('error', Yii::t('perm', 'perm-status-banned'));
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('perm', 'perm-word-error'));
            }
        }
    }

    /**
     * Action to fire group at Group Page
     *
     * @param $id integer group_id to fire
     */
    public function actionGroupFire($id)
    {
        $group = AdminUser::findOne($id);
        if (!empty($group)) {
            $group->scenario = AdminUser::SCENARIO_STATUS_MODERATION;
            $group->status   = AdminUser::STATUS_FIRED;
            if ($group->save()) {
                $aUsers = $group->getUsersInGroupIdsArray();
                if ($aUsers) {
                    $userSessions = ControlUserSessions::find()->andWhere(['in', 'user_id', $aUsers])->all();
                    foreach ($userSessions as $userSession) {
                        try {
                            $userSession->delete();
                        } catch (\Throwable $e) {
                            Yii::$app->session->setFlash('error', $e->getMessage());
                        };
                    }
                };
                Yii::$app->session->setFlash('error', Yii::t('perm', 'perm-status-deleted'));
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('perm', 'perm-word-error'));
            }
        }
    }

    /**
     * Action to edit Group Settings
     * @return string|\yii\web\Response Group Settings Page
     */
    public function actionGroupEdit()
    {
        $model = new AdminUser;
        $id    = Yii::$app->request->get('id');
        if (!empty($id)) {
            $model = AdminUser::findOne($id);
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->scenario = AdminUser::SCENARIO_EDIT_GROUP;
            if ($model->save()) {
                return $this->redirect(Url::toRoute('groups'));
            };
        }

        return $this->render('group_edit', [
            'groupModel' => $model,
        ]);
    }

    /**
     * Action to show Users Page (manage users)
     * @return string Users Page
     */
    public function actionUsers()
    {
        $searchModel  = new AdminUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), AdminUser::GROUP_FLAG_IT_IS_USER);
        $passModel    = new AdminUser();

        return $this->render('users', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'passModel'    => $passModel,
        ]);
    }

    /**
     * Action to edit Users
     * @return string|\yii\web\Response User-Edit Page
     */
    public function actionUserEdit()
    {
        $model = new AdminUser;
        $id    = Yii::$app->request->get('id');
        if (!empty($id)) {
            $model = AdminUser::findOne($id);
        }
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            try {
                $pass = Yii::$app->request->post('password');
                if (!empty($pass)) {
                    $model->password_hash = Yii::$app->security->generatePasswordHash($pass);
                    $model->auth_key      = Yii::$app->security->generateRandomString();
                }
                $model->scenario = AdminUser::SCENARIO_EDIT_USER;
                if ($model->save()) {
                    return $this->redirect(Url::toRoute('users'));
                };
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('user_edit', [
            'userModel' => $model,
        ]);
    }

    /**
     * Action to activate users on Users Page
     *
     * @param $id integer user_id to activate
     */
    public function actionUserActivate($id)
    {
        $user = AdminUser::findOne($id);
        if (!empty($user)) {
            $user->scenario = AdminUser::SCENARIO_STATUS_MODERATION;
            $user->status   = AdminUser::STATUS_ACTIVE;
            if ($user->save()) {
                Yii::$app->session->setFlash('success', Yii::t('perm', 'perm-status-activated'));
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('perm', 'perm-word-error'));
            }
        }
    }

    /**
     * Action to ban users at Users Page
     *
     * @param $id integer user_id to ban
     */
    public function actionUserBan($id)
    {
        $user = AdminUser::findOne($id);
        if (!empty($user)) {
            $user->scenario = AdminUser::SCENARIO_STATUS_MODERATION;
            $user->status   = AdminUser::STATUS_BANNED;
            if ($user->save()) {
                $userSessions = ControlUserSessions::findAll(['user_id' => $id]);
                foreach ($userSessions as $userSession) {
                    try {
                        $userSession->delete();
                    } catch (\Throwable $e) {
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    };
                }
                Yii::$app->session->setFlash('error', Yii::t('perm', 'perm-status-banned'));
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('perm', 'perm-word-error'));
            }
        }
    }

    /**
     * Action to fire user at Users Page
     *
     * @param $id integer user_id to fire
     */
    public function actionUserFire($id)
    {
        $user = AdminUser::findOne($id);
        if (!empty($user)) {
            $user->scenario = AdminUser::SCENARIO_STATUS_MODERATION;
            $user->status   = AdminUser::STATUS_FIRED;
            if ($user->save()) {
                $userSessions = ControlUserSessions::findAll(['user_id' => $id]);
                foreach ($userSessions as $userSession) {
                    try {
                        $userSession->delete();
                    } catch (\Throwable $e) {
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    };
                }
                Yii::$app->session->setFlash('error', Yii::t('perm', 'perm-status-fired'));
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('perm', 'perm-word-error'));
            }
        }
    }

    /**
     * Action to show Groups/Users Structure Page
     * @return string Groups/Users Structure Page
     */
    public function actionStructure()
    {
        $dataProvider = new ActiveDataProvider([
            'query'      => AdminUser::find()->where(['group_flag' => AdminUser::GROUP_FLAG_IT_IS_GROUP]),
            'pagination' => false,
            'sort'       => false,
        ]);

        return $this->render('structure_basic', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Action to change users passwords at Users Page
     * @return \yii\web\Response success/unsuccess response
     */
    public function actionUserChangePass()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (isset($post['password']) && isset($post['AdminUser']['id'])) {
                try {
                    $user = AdminUser::findOne(['id' => $post['AdminUser']['id'], 'group_flag' => AdminUser::GROUP_FLAG_IT_IS_USER]);
                    if ($user) {
                        $user->password_hash    = Yii::$app->security->generatePasswordHash($post['password']);
                        $user->auth_key         = Yii::$app->security->generateRandomString();
                        $user->change_pass_date = new Expression('NOW()');
                        $user->scenario         = AdminUser::SCENARIO_CHANGE_PASS;
                        if ($user->save()) {
                            $userSessions = ControlUserSessions::findAll(['user_id' => $post['AdminUser']['id']]);
                            try {
                                foreach ($userSessions as $userSession) {
                                    $userSession->delete();
                                }
                                Yii::$app->session->setFlash('success', Yii::t('perm', 'perm-word-success'));
                            } catch (\Throwable $e) {
                                Yii::$app->session->setFlash('error', $e->getMessage());
                            }

                            return $this->redirect(Url::toRoute('users'));
                        }
                    }
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                } catch (\Throwable $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }
        Yii::$app->session->setFlash('error', Yii::t('perm', 'perm-word-error'));

        return $this->redirect(Url::toRoute('users'));
    }

    /**
     * Action to show Users Own Profile Page
     * @return string|\yii\web\Response Profile Page
     */
    public function actionProfile()
    {
        $id        = Yii::$app->user->getId();
        $userModel = AdminUser::findOne(['id' => $id, 'group_flag' => AdminUser::GROUP_FLAG_IT_IS_USER]);
        if (Yii::$app->request->isPost && $userModel->load(Yii::$app->request->post())) {
            $userModel->scenario = AdminUser::SCENARIO_PROFILE_EDIT;
            if ($userModel->save()) {
                return $this->redirect('/');
            }
        }

        return $this->render('profile', [
            'userModel' => $userModel,
        ]);
    }

    /**
     * Action to show Permissions Page
     *
     * @param $id integer instance (group or user) id to change permissions for
     *
     * @return string
     */
    public function actionIndPermissions($id)
    {

        try {
            $controlPermission = new ControlPermission();
            $controlPermission->renewAuthItemBase($this->module->predefinedPermissions);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        $model                              = AdminUser::findOne($id);
        $searchModel                        = new ControlPermissionsSearch();
        $dataProvider                       = $searchModel->search(Yii::$app->request->queryParams, $id);
        $dataProvider->pagination->pageSize = 1000;

        return $this->render('ind_permissions_edit', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'model'        => $model,
        ]);
    }

    /**
     * Action to change Permission Status
     * @return bool success or unsuccess
     */
    public function actionChange()
    {
        if (Yii::$app->request->isPost) {
            $permission = Yii::$app->request->post('permission');
            $enabled    = Yii::$app->request->post('enabled');
            $user_id    = Yii::$app->request->post('user_id');
            if (!empty($permission) && !empty($user_id)) {
                TagDependency::invalidate(Yii::$app->cache,[Module::CACHE_TAG]);
                //Меняем пермишн
                return ControlPermissions::addNewUserPermission($permission, $user_id, $enabled);
            }
        }

        return false;
    }

    /**
     * Action to flush permission status to disallow
     * @return bool success or unsuccess
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isPost) {
            $permission = Yii::$app->request->post('permission');
            $user_id    = Yii::$app->request->post('user_id');
            if (!empty($permission) && !empty($user_id)) {
                TagDependency::invalidate(Yii::$app->cache,[Module::CACHE_TAG]);
                //Удаляем пермишн
                return ControlPermissions::addDeleteUserPermission($permission, $user_id);
            }
        }

        return false;
    }
}
