<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 06.02.18
 * Time: 14:51
 */

namespace common\components\PerfectParser\DataSources\MedFusion\resources\data\complex;


use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TArray;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TCode;
use common\components\PerfectParser\DataSources\MedFusion\resources\data\primitive\TString;

/**
 * Class THumanName
 * @package common\components\PerfectParser
 */
class THumanName extends TComplex
{
    /**
     * @var string
     */
    public $defaultFormat = '{name()}';
    /** @var TCode|null usual | official | temp | nickname | anonymous | old | maiden */
    public $use;
    /** @var TString[]|TArray|null Text representation of the full name */
    public $text;
    /** @var TString[]|TArray|null Family name (often called 'Surname') */
    public $family;
    /** @var TString[]|TArray|null Given names (not always 'first'). Includes middle names */
    public $given;
    /** @var TString[]|TArray|null Parts that come before the name */
    public $prefix;
    /** @var TString[]|TArray|null Parts that come after the name */
    public $suffix;
    /** @var TPeriod|null Time period when name was/is in use */
    public $period;

    /**
     * @return array
     */
    public function structure()
    {
        return [
            ['use', TCode::class],
            [['text', 'family', 'given', 'prefix', 'suffix'], [TString::class]],
            ['period', TPeriod::class]
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $silent = false)
    {
        if (is_string($data)) {
            $data = ['text' => $data];
        }
        return parent::load($data, $silent);
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function format_name(){
        if ($this->text) {
            $data = $this->text->first();
            if ($data && $data->getValue()){
                return $data->getValue();
            }
        }

        $result = [];
        $concatenate = function($set) use (&$result) {
            foreach ($set as $item) {
                /** @var TString $item */
                $piece = $item->getValue();
                if ($piece) {
                    $result[] = $piece;
                }
            }
        };

        foreach ([$this->prefix, $this->given, $this->family, $this->suffix] as $set) {
            if (!empty($set)) {
                $concatenate($set);
            }
        }

        $name = implode(' ', $result);
        $name = str_replace(["\r", "\n", "\t"], ' ', $name);
        $name =  preg_replace('!\s+!', ' ', $name);
        $name =  trim($name);
        return $name;
    }
}