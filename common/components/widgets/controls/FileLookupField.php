<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 16.11.17
 * Time: 03:09
 */

namespace common\components\widgets\controls;


use common\components\View;
use common\models\Insurance;
use yii\helpers\Html;

/**
 * Class FileLookupField
 * @package common\components\widgets\controls
 * @property View $view The view object that can be used to render views or view files. Note that the
 */
class FileLookupField extends BaseLookupField
{

    /** @var  mixed $attribute */
    public $attribute = 'patient_files_id';

    /** @var string $variantAttribute */
    public $variantAttribute = 'insurance_file_id';

    /** @var string $buttonId id of button for click to select and uploading file */
    public $buttonId;

    /**
     * @var array
     */
    public $pluginOptions = [];

    /**
     */
    public function init()
    {
        $this->pluginOptions = [
            'customFields' => ['id', 'link'],
            'editable' => false,
            'itemTemplate' => '<div class="option radio" data-id="{{id}}"><a href="{{link}}" class="fancy">{{item}}</a></div>',
            'valueSelector' => '#'.$this->getId().'_hidden',
            'valueField' => 'id'
        ];

        parent::init();

        $this->options = [];

        foreach ($this->variants as $variant) {
            /** @var Insurance $variant */

            $value = $variant->getAttribute($this->variantAttribute);

            if (!empty($value)) {
                $href = $variant->getHref();
                switch (strtolower($variant->file->file_extension)) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        $href = '/subscriber-home/preview-image?url=' . base64_encode($href);
                        break;
                }

                $this->options[] = [
                    'id' => $value,
                    'value' => 'Image from '.$variant->file->practice->practice_name,
                    'link' => $href
                ];
            }
        }
    }

    /**
     * @param bool $autoGenerate
     *
     * @return string
     */
    public function getId($autoGenerate = true)
    {
        return Html::getInputId($this->model, 'child_'.$this->attribute);
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->registerSelect();
        $this->view->registerModalScript('$(".fancy").fancybox({
            type: "iframe",
            iframe: {scrolling: "yes", preload: true},
        });');

        return $this->render('file-lookup-field', [
            'model'    => $this->model,
            'attribute'=> $this->attribute,
            'id'    => $this->getId(),
            'buttonId' => $this->buttonId,
        ]);
    }
}