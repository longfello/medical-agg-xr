<?php

namespace common\helpers;

/**
 * Class SimpleXML
 * @package common\helpers
 */
class SimpleXML
{
    /** This method disables entities loading from outside source and load content
     * @param string $content input string parameter
     * @return \SimpleXMLElement
     */
    public static function loadString($content)
    {
        libxml_disable_entity_loader(true);
        libxml_use_internal_errors(true);
        return SimpleXML_Load_String($content);
    }
}
