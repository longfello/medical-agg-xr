<?php
/**
 * Created by PhpStorm.
 * User: bohdan
 * Date: 22.10.18
 * Time: 15:28
 */

namespace common\components\PerfectParser\DataSources\CCDA\resources\labs;

/**
 * Class Rnte
 */
class RNte extends SubResource
{
    /** @var  */
    public $act;

    /**
     * @return null|string
     */
    public function getNte()
    {
        $result = null;

        if (isset($this->act->text->reference['value'])) {
            $referenceId = (string) $this->act->text->reference['value'];
            $result = (string) $this->getReferencedData($referenceId);
        } elseif (!empty(strval($this->act->text)) || strval($this->act->text) === '0') {
            $result = strval($this->act->text);
        }

        $result = trim($result);

        return $result;
    }
}