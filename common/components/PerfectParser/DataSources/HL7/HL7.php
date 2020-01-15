<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 30.05.18
 * Time: 16:08
 */

namespace common\components\PerfectParser\DataSources\HL7;


use common\components\PerfectParser\Common\Prototype\DataSource;
use common\models\Partners;

/**
 * Class source
 * @package common\components\PerfectParser
 */
class HL7 extends DataSource
{
    /**
     * @inheritdoc
     */
const ID = 'Hl7';

    /**
     * @inheritdoc
     */
const PARTNER_ID=Partners::PARTNER_EMR;
}