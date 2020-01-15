<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 14.11.17
 * Time: 13:59
 */

namespace common\components\editors;

use common\models\Settings;
use yii\base\InvalidArgumentException;
use yii\base\ViewContextInterface;
use yii\bootstrap\Html;
use yii\helpers\BaseInflector;

/**
 * Class prototype
 * @package common\components\editors
 */
class prototype extends Settings implements ViewContextInterface
{
    /**
     * @var
     */
    private $_view;

    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['value'], 'safe'];
        return $rules;
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderEditor($options = [])
    {
        return $this->render('default', [
            'model' => $this,
            'options' => $options
        ]);

    }

    /**
     * @return string
     */
    public function renderValue(){
        $value = $this->value ? $this->value : "<span class='text-danger'>( empty value )</span>";
        return $value . Html::a(
                Html::tag('span', '', ['class' => "glyphicon glyphicon-pencil"]),
                [
                    '/admin/update-settings',
                    'key' => $this->key,
                ],
                [
                    'class' => 'js-popup pull-right',
                    'title' => "Update {$this->name}",
                    'data-pjax' => 0,
                ]
            ). Html::tag('div', '', ['class' => 'clearfix']);
    }

    /**
     * @return string
     */
    public function getID(){
        return BaseInflector::slug(get_called_class().'-'.uniqid());
    }
    /**
     * Returns the directory containing the view files for this widget.
     * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
     *
     * @throws \ReflectionException if the class does not exist.
     * @return string the directory containing the view files for this widget.
     */
    public function getViewPath()
    {
        $class = new \ReflectionClass($this);

        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * Renders a view.
     *
     * The view to be rendered can be specified in one of the following formats:
     *
     * - [path alias](guide:concept-aliases) (e.g. "@app/views/site/index");
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
     *   active module.
     * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
     *
     * If the view name does not contain a file extension, it will use the default one `.php`.
     *
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidArgumentException if the view file does not exist.
     */
    public function render($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    /**
     * Returns the view object that can be used to render views or view files.
     * The [[render()]] and [[renderFile()]] methods will use
     * this view object to implement the actual view rendering.
     * If not set, it will default to the "view" application component.
     * @return \yii\web\View the view object that can be used to render views or view files.
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = \Yii::$app->getView();
        }

        return $this->_view;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function prepareGetValue($value)
    {
        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function prepareSetValue($value)
    {
        return $value;
    }
}