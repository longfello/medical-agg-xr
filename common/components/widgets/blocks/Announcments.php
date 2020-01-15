<?php

namespace common\components\widgets\blocks;

use common\models\Announcements;
use yii\base\Widget;

/**
 * Class Forgot
 * @package common\components\widgets
 */
class Announcments extends Widget
{
    /**
     *
     */
    const VIEW_INDEX = "index";
    /**
     *
     */
    const VIEW_PREVIEW = "_preview";
    /** @var int|false Limit num results */
    public $limit = 3;
    /**
     * @var string
     */
    public $view = self::VIEW_INDEX;
    /**
     * @var int
     */
    public $count = 0;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $config = ['limit' => $this->limit];
        if($this->view == self::VIEW_PREVIEW){
            $config['count'] = Announcements::countAnnouncements();
        }
        else {
            $config['list'] = Announcements::getAnnouncments();
        }
        return $this->render( "Announcements/{$this->view}", $config);
    }

}
