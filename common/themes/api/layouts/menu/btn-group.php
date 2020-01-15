<?php
/**
 * @var $menu array
 * @var $this \yii\web\View
 */

$buttons = '';
foreach ($menu as $one) {
    if ($one) {
        $one['class'] = isset($one['class']) ? $one['class'] : '';
        $one['class'] .= ' btn';

        $urls = isset($one['item-hrefs']) ? $one['item-hrefs'] : [];
        $urls[] = $one['href'];

        $urls = array_map(function ($item) {
            $item = str_replace(getenv("WWW_HOST"), '', $item);
            $item = str_replace(getenv("WWW_SCHEME") . '://', '', $item);
            return $item;
        }, $urls);

        $active = (in_array('/' . \Yii::$app->request->pathInfo, $urls));

        $one['class'] .= $active ? ' hr-nav__btn hr-nav__btn--active' : ' hr-nav__btn';
        $title = $one['title'];
        if (isset($one['mobile-title'])) {
            $title = "<span class='title-desktop'>{$title}</span><span class='title-mobile'>{$one['mobile-title']}</span>";
        }
        $url = $one['href'];

        unset($one['title'], $one['href'], $one['mobile-title']);

        if (isset($one['tooltip'])) {
            $one['data-toggle'] = 'tooltip';
            $one['data-placement'] = 'bottom';
            $one['title'] = $one['tooltip'];
            $one['data-container'] = 'body';
            unset($one['tooltip']);
        }

        $buttons .= \yii\helpers\Html::a($title, $url, $one);
    } else {
        $buttons .= "</div><div class='hr-nav hr-menu__nav' style='margin-left: 10px;'>";
    }
}
$html = '<div class="hr-nav hr-menu__nav">' . $buttons . '</div>';

echo($html);
