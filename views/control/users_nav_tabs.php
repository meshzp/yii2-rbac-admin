<?php

use meshzp\rbacadmin\components\PermHtml as Html;

/**
 * @var $id integer
 * @var $active integer
 * @var $user_id integer
 * @var $class array
 */

$class[$active] = ' class="active" ';
?>

<ul class="nav nav-tabs">
    <?php if ((isset($class[4])) || isset($class[5])) : ?>
        <li<?= isset($class[0]) ? $class[0] : "" ?>><?= Html::a(Yii::t('perm', 'perm-user-list'), ['control/users']) ?></li>
        <li<?= isset($class[4]) ? $class[4] : "" ?>><?= Html::a(Yii::t('perm', 'perm-user-edit'), ['control/user-edit', 'id' => $user_id]) ?>
        <li<?= isset($class[5]) ? $class[5] : "" ?>><?= Html::a(Yii::t('perm', 'perm-user-permissions'), ['control/ind-permissions', 'id' => $user_id]) ?></li>
    <?php else: ?>
        <li<?= isset($class[0]) ? $class[0] : "" ?>><?= Html::a(Yii::t('perm', 'perm-user-list'), ['control/users']) ?></li>
        <li<?= isset($class[1]) ? $class[1] : "" ?>><?= Html::a(Yii::t('perm', 'perm-structure'), ['control/structure']) ?></li>
        <li<?= isset($class[3]) ? $class[3] : "" ?>><?= Html::a(Yii::t('perm', 'perm-user-manager'), ['control/user-manager']) ?></li>
    <?php endif; ?>
</ul>
