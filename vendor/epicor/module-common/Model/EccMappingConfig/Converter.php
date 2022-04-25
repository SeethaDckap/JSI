<?php
/**
 * Converter of attributes configuration from \DOMDocument to array
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\EccMappingConfig;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $result = [];
        /** @var DOMNode $groupNode */
        foreach ($source->documentElement->childNodes as $groupNode) {
            if ($groupNode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $groupName = $groupNode->attributes->getNamedItem('name')->nodeValue;
            $groupLabel = $groupNode->attributes->getNamedItem('label')->nodeValue;

            $result[$groupName] = $groupLabel;
        }
        return $result;
    }
}
