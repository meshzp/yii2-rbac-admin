<?php

namespace meshzp\rbacadmin\components;

use meshzp\rbacadmin\components\MenuDropdown as Dropdown;
use meshzp\rbacadmin\components\PermHtml as Html;
use yii\base\InvalidConfigException;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Class MenuNav - wrapper for standard yii2 Nav class
 * @package meshzp\rbacadmin\components
 */
class MenuNav extends Nav
{

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
        $view = $this->getView();
        NavAsset::register($view);
    }

    /**
     * Renders a widget's item.
     *
     * @param string|array $item the item to render.
     *
     * @return string the rendering result.
     * @throws InvalidConfigException
     */
    public function renderItem($item)
    {
        if (is_string($item)) {
            return $item;
        }
        if (!isset($item['label'])) {
            throw new InvalidConfigException("The 'label' option is required.");
        }
        $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
        $label       = $encodeLabel ? Html::encode($item['label']) : $item['label'];
        $options     = ArrayHelper::getValue($item, 'options', []);
        $items       = ArrayHelper::getValue($item, 'items');
        $url         = ArrayHelper::getValue($item, 'url', '#');
        $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);

        if (isset($item['active'])) {
            $active = ArrayHelper::remove($item, 'active', false);
        } else {
            $active = $this->isItemActive($item);
        }

        if (empty($items)) {
            $items     = '';
            $haveChild = 0;
        } else {
            $haveChild                  = 1;
            $linkOptions['data-toggle'] = 'dropdown';
            Html::addCssClass($options, ['widget' => 'dropdown']);
            Html::addCssClass($linkOptions, ['widget' => 'dropdown-toggle']);
            if ($this->dropDownCaret !== '') {
                $label .= ' ' . $this->dropDownCaret;
            }
            if (is_array($items)) {
                if ($this->activateItems) {
                    $items = $this->isChildActive($items, $active);
                }
                $items = $this->renderDropdown($items, $item);
            }
        }

        if ($this->activateItems && $active) {
            Html::addCssClass($options, 'active');
        }

        if ($haveChild == 1 && $items == '') {
            return '';
        } else {
            return Html::tag('li', Html::a($label, $url, $linkOptions) . $items, $options);
        }
    }

    /**
     * Renders the given items as a dropdown.
     * This method is called to create sub-menus.
     *
     * @param array $items the given items. Please refer to [[Dropdown::items]] for the array structure.
     * @param array $parentItem the parent item information. Please refer to [[items]] for the structure of this array.
     *
     * @return string the rendering result.
     */
    protected function renderDropdown($items, $parentItem)
    {
        $result = '';
        try{
            $result = Dropdown::widget([
                'options'       => ArrayHelper::getValue($parentItem, 'dropDownOptions', []),
                'items'         => $items,
                'encodeLabels'  => $this->encodeLabels,
                'clientOptions' => false,
                'view'          => $this->getView(),
            ]);
        } catch (\Exception $e){
            Yii::$app->session->setFlash('error', 'Dropdown menu can not be rendered because of error happened: ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * Getting array of default Menu items for RBACAdmin module
     * @return array
     */
    public static function RbacManagementMenuItems(){
        return [
            [
                'label' => 'Settings', 'url' => '/rbacadmin/settings/index',
            ],
            [
                'label' => 'Users', 'url' => '/rbacadmin/control/users',
            ],
            [
                'label' => 'Groups', 'url' => '/rbacadmin/control/groups',
            ],
            [
                'label' => 'Request Logs', 'url' => '/rbacadmin/log/index',
            ],
        ];
    }
}
