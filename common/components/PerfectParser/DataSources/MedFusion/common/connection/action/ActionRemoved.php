<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.03.18
 * Time: 15:22
 */

namespace common\components\PerfectParser\DataSources\MedFusion\common\connection\action;

/**
 * Class ActionRemoved
 * @package common\components\PerfectParser
 */
class ActionRemoved extends prototype
{
    /**
     * @var
     */
    public $old_status;

    /** @var $portalID int */
    public $portalID;

    /**
     * @inheritdoc
     */
    public function process(){
    }
}