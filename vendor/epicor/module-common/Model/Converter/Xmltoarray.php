<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Converter;


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
    protected $_xmlDoc;

    /**
     * Convert xml String to Array
     * 
     * @param string $xml
     * @return array
     */
    public function xml2Array($xml)
    {
        $xml_array = array();
        $this->_xmlDoc = new \DOMDocument();
        $this->_xmlDoc->preserveWhiteSpace = true;
        @$this->_xmlDoc->loadXML($xml);
        $xml_array = @$this->createArray($this->_xmlDoc->documentElement);
        if (count($xml_array) === 0)
            $xml_array = $xml;

        return $xml_array;
    }

    /**
     * Convert Array to xml String
     * 
     * @param array $data
     * @return string
     */
    public function array2Xml($data)
    {
        $xml = '';
        //create the xml document
        $this->_xmlDoc = new \DOMDocument();

        //create xml body
        $this->createXml($data, $this->_xmlDoc);
        $this->_xmlDoc->formatOutput = true;
        return @$this->_xmlDoc->saveXML();
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

    protected function createXml($data, $node)
    {
        if (is_array($data)) {
            foreach ($data as $element => $value) {
                $tag = $node->appendChild($this->_xmlDoc->createElement($element));
                $this->addAttributes($tag, $value);
                if (is_array($value)) {
                    $numItems = count($value);
                    $i = 0;
                    foreach ($value as $key => $key_value) {
                        if (!is_int($key)) {
                            $this->createXml($value, $tag);
                            break;
                        } else if (is_int($key) && !is_array($key_value)) {
                            if ($key == 0) {
                                $tag->appendChild($this->_xmlDoc->createTextNode($key_value));
                            } else {
                                $this->createXml(array($element => $key_value), $node);
                            }
                        } else {
                            $this->addAttributes($tag, $key_value);
                            $this->createXml($key_value, $tag);
                            if (++$i !== $numItems) {
                                $tag = $node->appendChild($this->_xmlDoc->createElement($element));
                            }
                        }
                    }
                } elseif ($value != null) {
                    $tag->appendChild($this->_xmlDoc->createTextNode($value));
                }
            }
        }
    }

    protected function addAttributes(&$tag, &$value)
    {

        if (is_array($value) && isset($value['_attributes'])) {
            foreach ($value['_attributes'] as $attr => $attr_value) {
                $tag->appendChild(
                    $this->_xmlDoc->createAttribute($attr))->appendChild(
                    $this->_xmlDoc->createTextNode($attr_value));
            }
            unset($value['_attributes']);

            if (count($value) == 1) {
                $keys = array_keys($value);
                if ($keys[0] == 0 && isset($value[0]) && !is_array($value[0])) {
                    $tag->appendChild($this->_xmlDoc->createTextNode($value[0]));
                    $value = null;
                }
            }
        }
    }

    protected function createArray($node)
    {
        $node_tagName = $node->tagName;
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                return $node->textContent;
                break;


            case XML_ELEMENT_NODE:
                $output[$node_tagName] = array();

                // loop through the attributes and collect them
                $attributes = array();
                foreach ($node->attributes as $attrName => $attrNode) {
                    $attributes[$attrName] = $attrNode->value;
                }
                if (count($attributes) > 0)
                    $output[$node_tagName]['_attributes'] = $attributes;


                // for each child node, call the covert function recursively
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $tag_name = $child->tagName;
                    $child_array = $this->createArray($child, $tag_name);
                    if (isset($child_array['']))
                        $output[$node_tagName] = $child_array[''];
                    else {
                        $keys = array_keys($child_array);
                        if (isset($output[$node_tagName][$keys[0]])) {
                            if (isset($output[$node_tagName][$keys[0]][0]))
                                $orig_array = $output[$node_tagName][$keys[0]];
                            else
                                $orig_array = array($output[$node_tagName][$keys[0]]);

                            $output[$node_tagName][$keys[0]] = array_merge($orig_array, array($child_array[$keys[0]]));
                        } else
                            $output[$node_tagName] = array_merge($output[$node_tagName], $child_array);
                    }
                }
                if (is_array($output[$node_tagName]) && count($output[$node_tagName] == 0))
                    $output[$node_tagName] = null;


                break;

            default:
                return '';
        }
        return $output;
    }

}
