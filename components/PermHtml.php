<?php

namespace meshzp\rbacadmin\components;

use Yii;
use yii\helpers\Html;
use yii\web\Request;

/**
 * Class PermHtml - wrapper for standard yii2 Html class
 * @package meshzp\rbacadmin\components
 */
class PermHtml extends Html
{
    /**
     * @inheritdoc
     */
    public static function a($text, $url = null, $options = [])
    {
        if (!is_array($url)) {
            $url = [$url];
        }
        $normalizedRoute = static::normalizeRoute($url[0]);
        $manager         = Yii::$app->getAuthManager();
        if ($normalizedRoute && $manager->checkAccess(Yii::$app->user->getId(), $normalizedRoute)) {
            $result = parent::a($text, $url, $options);

            return $result;
        }

        return '';
    }

    /**
     * Normalizes route
     *
     * @param $route
     *
     * @return bool|string
     */
    protected static function normalizeRoute($route)
    {
        if ($route === '' || $route === '#') {
            return '/' . Yii::$app->controller->getRoute();
        } elseif (strncmp($route, '/', 1) === 0) {
            return $route;
        } elseif (strpos($route, '/') === false) {
            return '/' . Yii::$app->controller->getUniqueId() . '/' . $route;
        } elseif (($mid = Yii::$app->controller->module->getUniqueId()) !== '') {
            return '/' . $mid . '/' . $route;
        }
        $parsedRoute = Yii::$app->urlManager->parseRequest(new Request(['url' => $route]));
        if (isset($parsedRoute[0])) {
            return '/' . $parsedRoute[0];
        } else {
            return false;
        }
    }



    /**
     * Filter action column button. Use with [[yii\grid\GridView]]
     * ```php
     * 'columns' => [
     *     ...
     *     [
     *         'class' => 'yii\grid\ActionColumn',
     *         'template' => Helper::filterActionColumn(['view','update','activate'])
     *     ]
     * ],
     * ```
     * @param array $buttons
     * @return mixed|string
     */
    public static function filterActionColumn($buttons = [])
    {
        if (is_array($buttons)) {
            $result = [];
            foreach ($buttons as $button) {
                if (Yii::$app->user->can(static::normalizeRoute($button))) {
                    $result[] = "{{$button}}";
                }
            }
            return implode(' ', $result);
        }
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) {
            return Yii::$app->user->can(static::normalizeRoute($matches[1])) ? "{{$matches[1]}}" : '';
        }, $buttons);
    }
}
