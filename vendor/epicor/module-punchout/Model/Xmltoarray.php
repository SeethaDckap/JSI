<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;


/**
 * Usage :
 *
 *  $xmlClass = new Epicor_Common_Model_XmlToArray();
 *  $xmlClass = Mage::getModel('common/xmltoarray');
 *  $arry = $xmlClass->xml2Array($xml);
 *  $xml = $xmlClass->array2Xml($arry);
 *
 */
class Xmltoarray
{

    /**
     *
     * @var \DOMDocument
     */
    protected $xmlDoc;

    /**
     * Convert xml String to Array
     *
     * @param string $xml
     * @return array
     */
    public function xml2Array($xml)
    {
        $this->xmlDoc = new \DOMDocument();
        $this->xmlDoc->preserveWhiteSpace = true;
        @$this->xmlDoc->loadXML($xml);
        $xmlArray = @$this->createArray($this->xmlDoc->documentElement);
        if (count($xmlArray) === 0) {
            $xmlArray = $xml;
        }

        return $xmlArray;
    }

    /**
     * Convert Array to xml String
     *
     * @param array $data
     * @return string
     */
    public function array2Xml($data)
    {
        //create the xml document
        $this->xmlDoc = new \DOMDocument();

        $implementation = new \DOMImplementation();
        $this->xmlDoc->appendChild(
            $implementation->createDocumentType(
                ' cXML SYSTEM "http://xml.cXML.org/schemas/cXML/1.2.011/cXML.dtd"'
            )
        );

        //create xml body
        $this->createXml($data, $this->xmlDoc);
        $this->xmlDoc->formatOutput = true;
        return @$this->xmlDoc->saveXML();
    }

    /**
     * Convert xml String to html safe String
     *
     * @param string $xml
     * @return string
     */
    public function xmlToHtml($xml = '')
    {
        $dom = new \DOMDocument();

        $dom->preserveWhiteSpace = false;
        @$dom->loadXML($xml);
        $dom->formatOutput = true;
        return nl2br(str_replace(" ", '&nbsp;&nbsp;&nbsp;', htmlentities($dom->saveXML())));
    }

    /**
     * Create Xml
     *
     * @param array $data
     * @param object $node
     * @return object
     */
    protected function createXml($data, $node)
    {
        if (is_array($data)) {
            foreach ($data as $element => $value) {
                $tag = $node->appendChild($this->xmlDoc->createElement($element));
                $this->addAttributes($tag, $value);
                if (is_array($value)) {
                    $tag = $this->processArrayvalue($node, $tag, $value, $element);
                } elseif ($value != null) {
                    $tag->appendChild($this->xmlDoc->createTextNode($value));
                }
            }
        }
    }

    /**
     * Process Array Value
     *
     * @param object $node
     * @param object $tag
     * @param string $value
     * @param object $element
     * @return object
     */
    protected function processArrayvalue($node, $tag, $value, $element)
    {
        $numItems = count($value);
        $i = 0;
        foreach ($value as $key => $keyValue) {
            if (!is_int($key)) {
                $this->createXml($value, $tag);
                break;
            } else if (is_int($key) && !is_array($keyValue)) {
                if ($key == 0) {
                    $tag->appendChild($this->xmlDoc->createTextNode($keyValue));
                } else {
                    $this->createXml(array($element => $keyValue), $node);
                }
            } else {
                $this->addAttributes($tag, $keyValue);
                $this->createXml($keyValue, $tag);
                if (++$i !== $numItems) {
                    $tag = $node->appendChild($this->xmlDoc->createElement($element));
                }
            }
        }
        return $tag;
    }

    /**
     * Add Attribute to xml tags
     *
     * @param object $tag
     * @param string $value
     */
    protected function addAttributes(&$tag, &$value)
    {
        $_attributes = '_attributes';
        if (is_array($value) && isset($value[$_attributes])) {
            foreach ($value[$_attributes] as $attr => $attrValue) {
                $tag->appendChild(
                    $this->xmlDoc->createAttribute($attr))->appendChild(
                    $this->xmlDoc->createTextNode($attrValue));
            }
            unset($value[$_attributes]);

            if (count($value) == 1) {
                $keys = array_keys($value);
                if ($keys[0] == 0 && isset($value[0]) && !is_array($value[0])) {
                    $tag->appendChild($this->xmlDoc->createTextNode($value[0]));
                    $value = null;
                }
            }
        }
    }

    /**
     * Create a array from Xml
     *
     * @param object $node
     * @return array
     */
    protected function createArray($node)
    {
        $nodeTagName = $node->tagName;
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                return $node->textContent;
                break;


            case XML_ELEMENT_NODE:
                $output[$nodeTagName] = array();

                // loop through the attributes and collect them
                $output = $this->loopAttributes($node, $nodeTagName, $output);
                // for each child node, call the covert function recursively
                if (is_array($output[$nodeTagName]) && count($output[$nodeTagName] == 0)) {
                    $output[$nodeTagName] = null;
                }


                break;

            default:
                return '';
        }
        return $output;
    }

    /**
     * Process the Attribute
     *
     * @param object $node
     * @param string $nodeTagName
     * @param array $output
     * @return array
     */
    protected function loopAttributes($node, $nodeTagName, $output)
    {
        $attributes = array();
        foreach ($node->attributes as $attrName => $attrNode) {
            $attributes[$attrName] = $attrNode->value;
        }
        if (!empty($attributes)) {
            $output[$nodeTagName]['_attributes'] = $attributes;
        }
        for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
            $child = $node->childNodes->item($i);
            $tagName = $child->tagName;
            $childArray = $this->createArray($child, $tagName);
            if (isset($childArray[''])) {
                $output[$nodeTagName] = $childArray[''];
            } else {
                $keys = array_keys($childArray);
                if (isset($output[$nodeTagName][$keys[0]])) {
                    if (isset($output[$nodeTagName][$keys[0]][0])) {
                        $origArray = $output[$nodeTagName][$keys[0]];
                    } else {
                        $origArray = array($output[$nodeTagName][$keys[0]]);
                    }

                    $output[$nodeTagName][$keys[0]] = array_merge($origArray, array($childArray[$keys[0]]));
                } else {
                    $output[$nodeTagName] = array_merge($output[$nodeTagName], $childArray);
                }
            }
        }
        return $output;

    }

}
