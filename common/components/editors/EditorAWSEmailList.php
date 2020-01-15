<?php

namespace common\components\editors;

use yii\base\Model;
use yii\base\ViewContextInterface;
use yii\helpers\BaseInflector;
use yii\helpers\Html;
use yii\base\DynamicModel;
use common\components\AWSParam;
use yii\base\InvalidArgumentException;

/**
 * Class EditorAWSEmailList
 *
 * Important! Needs init key and name attributes via constructor!
 *
 * @package common\components\editors\
 */
class EditorAWSEmailList extends Model implements ViewContextInterface {
    /** @var string */
    public $name;
    /** @var string */
    public $key;
    /** @var array[]  */
    public $emailList = [];
    /** @var string */
    public $value;

    /** @var string */
    private $_view;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $param = new AWSParam();
        $this->value = trim($param->get($this->key));
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['emailList'], 'validateEmails'],
        ];
    }

    /**
     * Validate each email value in $emailList attribute
     * Validator add error to model and use array index as attribute key
     *
     * @param $attribute
     * @param $params
     */
    public function validateEmails($attribute, $params) {
        // skip empty values in email list
        //$this->{$attribute} = array_filter($this->{$attribute});

        if (count($this->{$attribute})) {
            // cast numerical array keys to string because Yii validator needs attributes as assoc array
            $keys = explode(',', implode("-item,", array_keys($this->{$attribute})) . '-item');
            $this->{$attribute} = array_combine($keys, $this->{$attribute});

            // run validation via DynamicModel
            $validatorModel = new DynamicModel($this->{$attribute});
            $validatorModel->addRule(array_keys($this->{$attribute}), 'email', [
                    'skipOnError' => false,
                    'message' => 'This is not a valid email address.',
                ]
            );
            $validatorModel->addRule(array_keys($this->{$attribute}), 'required', [
                    'skipOnError' => false,
                    'message' => 'Field can\'t be empty.',
                ]
            );
            if (!$validatorModel->validate()) {
                $this->addErrors($validatorModel->getErrors());
            }
        }
    }

    /**
     * Save AWS param value
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function save() {
        $value = implode(', ', $this->emailList);

        //fix AWS S3 Client validation exception when try to save empty value
        if (empty($value)) {
            $value = ' ';
        }

        $params = new AWSParam();
        return $params->set($this->key, $value);
    }

    /**
     * Render emails editor form view
     * @param array $options
     * @return string
     */
    public function renderEditor($options = []) {
        // init $emailList attribute from setting value
        if (!empty($this->value) && 0 === count($this->emailList)) {
            $this->emailList = array_map('trim', explode(',', $this->value));
        }
        return $this->render('emaillist', [
            'model' => $this,
            'options' => $options,
        ]);
    }

    /**
     * @return string
     */
    public function renderValue(){
        if (!empty($this->value)) {
            $value = array_map('trim', explode(',', $this->value));
            $value = implode('<br/>', $value);
        } else {
            $value = '<span class="text-danger">( empty value )</span>';
        }
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
    public function getID() {
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