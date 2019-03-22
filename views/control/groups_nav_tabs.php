<?php

use meshzp\rbacadmin\components\PermHtml as Html;

/**
 * @var $id integer
 * @var $active integer
 * @var $group_id integer
 * @var $class array
 */

$class[$active] = ' class="active" ';
?>

<ul class="nav nav-tabs">
    <?php if ((isset($class[1])) || isset($class[2])) : ?>
        <li<?= isset($class[0]) ? $class[0] : "" ?>><?= Html::a(Yii::t('perm', 'perm-group-list'), ['control/groups']) ?></li>
        <li<?= isset($class[1]) ? $class[1] : "" ?>><?= Html::a(Yii::t('perm', 'perm-group-edit'), ['control/group-edit', 'id' => $group_id]) ?></li>
        <li<?= isset($class[2]) ? $class[2] : "" ?>><?= Html::a(Yii::t('perm', 'perm-group-permissions'), ['control/ind-permissions', 'id' => $group_id]) ?></li>
    <?php else: ?>
        <li<?= isset($class[0]) ? $class[0] : "" ?>><?= Html::a(Yii::t('perm', 'perm-group-list'), ['control/groups']) ?></li>
    <?php endif; ?>

</ul>
