<?php

namespace common\helpers;

use common\models\Practices;
use yii\helpers\ArrayHelper;

/**
 * Class PracticeNameList  Helpers for preparing list
 * @package common\helpers
 */
class PracticeNameList
{
    /**
     * Preparing list for generating filter option on view Query Users
     *
     * @var array $firstItem Option for reset filter
     * @var array $secondItem Option for search self-registered common\models\Patient where practice_id zero and NULL
     * @return array  List options in view
     */
    public static function getList()
    {
        $firstItem = [['practice_id' => 'all', 'practice_name' => 'All']];
        $secondItem = [['practice_id' => 'self', 'practice_name' => 'Self-Registered']];
        $listItem = Practices::getEnrollPracticeNameList();
        $result = array_merge($firstItem, $secondItem, $listItem);
        return ArrayHelper::map($result, 'practice_id', 'practice_name');
    }

}

