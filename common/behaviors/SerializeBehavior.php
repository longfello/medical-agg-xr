<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 07.03.17
 * Time: 16:50
 */

namespace common\behaviors;


use baibaratsky\yii\behaviors\model\SerializeAttributeException;
use baibaratsky\yii\behaviors\model\SerializedAttributes;

/**
 * Class SerializeBehavior
 * @package common\behaviors
 */
class SerializeBehavior extends SerializedAttributes
{

    /**
     * @var array
     */
    private $oldAttributes = [];

    /**
     * @throws SerializeAttributeException
     */
    public function serializeAttributes()
    {
        foreach ($this->attributes as $attribute) {
            if (isset($this->oldAttributes[$attribute])) {
                $this->owner->setOldAttribute($attribute, $this->oldAttributes[$attribute]);
            }

            if ((is_array($this->owner->{$attribute}) && count($this->owner->{$attribute}) > 0) || is_object($this->owner->{$attribute})) {
                $this->owner->$attribute = serialize($this->owner->{$attribute});
                if ($this->encode) {
                    $this->owner->{$attribute} = base64_encode($this->owner->{$attribute});
                }
            } elseif (empty($this->owner->{$attribute})) {
                $this->owner->{$attribute} = null;
            } else {
                throw new SerializeAttributeException($this->owner, $attribute);
            }
        }
    }

    /**
     *
     */
    public function deserializeAttributes()
    {
        foreach ($this->attributes as $attribute) {
            $this->oldAttributes[$attribute] = $this->owner->getOldAttribute($attribute);

            if (empty($this->owner->{$attribute})) {
                $this->owner->setAttribute($attribute, []);
                $this->owner->setOldAttribute($attribute, []);
            } elseif (is_scalar($this->owner->{$attribute})) {
                if ($this->encode) {
                    $this->owner->{$attribute} = base64_decode($this->owner->{$attribute});
                }
                $value = @unserialize($this->owner->$attribute);
                if ($value !== false) {
                    $this->owner->setAttribute($attribute, $value);
                    $this->owner->setOldAttribute($attribute, $value);
                } else {
                    \Yii::info('Can’t deserialize attribute "' . $attribute . '" of "' . get_class($this->owner) . '".');
                    // throw new DeserializeAttributeException($this->owner, $attribute);
                }
            }
        }
    }
}