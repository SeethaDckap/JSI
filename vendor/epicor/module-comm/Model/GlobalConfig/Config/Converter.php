<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 4)
namespace Epicor\Comm\Model\GlobalConfig\Config;


class Converter implements \Magento\Framework\Config\ConverterInterface
{

    /**
     * Convert config
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        /** @var \DOMNode $node */
        foreach ($source->getElementsByTagName('global') as $node) {
            $output = array_merge($output, $this->_convertNode($node));
        }

        return $output;
    }

    /**
     * Convert node oto array
     *
     * @param \DOMNode $node
     * @param string $path
     * @return array|string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertNode(\DOMNode $node, $path = '')
    {
        $output = [];
        if ($node->nodeType == XML_ELEMENT_NODE) {
            $nodeData = [];
            /** @var $childNode \DOMNode */
            foreach ($node->childNodes as $childNode) {
                $childrenData = $this->_convertNode($childNode, ($path ? $path . '/' : '') . $childNode->nodeName);
                if ($childrenData == null) {
                    continue;
                }
                if (is_array($childrenData)) {
                    $nodeData = array_merge($nodeData, $childrenData);
                } else {
                    $nodeData = $childrenData;
                }
            }
            if (is_array($nodeData) && empty($nodeData)) {
                $nodeData = null;
            }
            $output[$node->nodeName] = $nodeData;
        } elseif ($node->nodeType == XML_CDATA_SECTION_NODE || $node->nodeType == XML_TEXT_NODE && trim(
                $node->nodeValue
            ) != ''
        ) {
            return $node->nodeValue;
        }

        return $output;
    }
}
//M1 > M2 Translation End