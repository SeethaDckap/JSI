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
class Xmltovarien
{

    /**
     *
     * @var \DOMDocument 
     */
    protected $_xmlDoc;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;
    protected $xmlcovert;

    public function __construct(
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\Convert\Xml $xmlcovert
    ) {
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
        $this->xmlcovert = $xmlcovert;
    }

    /**
     * Convert xml String to Array
     *
     * @param string $xml
     * @return Array
     * @throws \Exception
     */
    public function xml2Array($xml)
    {
        $ob= simplexml_load_string($xml);
        $json  = json_encode($ob);
        $configData = json_decode($json, true);
        $xmltoArray['messages'] = $configData;
        return $xmltoArray;
    }

    /**
     * Convert xml String to Varien Object
     *
     * @param string $xml
     * @return \Epicor\Common\Model\Xmlvarien
     * @throws \Exception
     */
    public function xml2Varien($xml)
    {
        $v_obj = $this->commonXmlvarienFactory->create();
        $this->_xmlDoc = new \DOMDocument();
        $this->_xmlDoc->preserveWhiteSpace = false;

        if (!$this->_xmlDoc->loadXML($xml)) {
            throw new \Exception('Invalid XML');
        }
        $v_obj = $this->createVarienObject($this->_xmlDoc->documentElement);
                 
        return $v_obj;
    }

    /**
     * Convert Varien Object to xml String
     * 
     * @param \Magento\Framework\DataObject $data
     * @return string
     */
    public function varien2Xml($data)
    {
        $xml = '';

        //create the xml document
        $this->_xmlDoc = new \DOMDocument();

        //create xml body
        $this->createXml($data, $this->_xmlDoc);

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
        if (is_a($data, 'Magento\Framework\DataObject')) {
            foreach ($data->getData() as $element => $value) {
                $tag = $node->appendChild($this->_xmlDoc->createElement($element));
                if (is_a($value, 'Magento\Framework\DataObject') && $value->hasData('_attributes')) {
                    foreach ($value->getData('_attributes')->getData() as $attr => $attr_value) {
                        $tag->appendChild(
                            $this->_xmlDoc->createAttribute($attr))->appendChild(
                            $this->_xmlDoc->createTextNode($attr_value));
                    }
                    $value->unsetData('_attributes');
                }
                if (is_a($value, 'Magento\Framework\DataObject')) {
                    $numItems = count($value->getData());
                    $i = 0;
                    foreach ($value->getData() as $key => $key_value) {
                        if (!is_int($key)) {
                            $this->createXml($value, $tag);
                            break;
                        }
                        $this->createXml($key_value, $tag);
                        if (++$i !== $numItems) {
                            $tag = $node->appendChild($this->_xmlDoc->createElement($element));
                        }
                    }
                } elseif (is_array($value)) {
                    $numItems = count($value);
                    $i = 0;
                    foreach ($value as $key => $key_value) {
                        if (!is_int($key)) {
                            $this->createXml($value, $tag);
                            break;
                        }
                        $this->createXml($key_value, $tag);
                        if (++$i !== $numItems) {
                            $tag = $node->appendChild($this->_xmlDoc->createElement($element));
                        }
                    }
                } else {
                    $tag->appendChild($this->_xmlDoc->createTextNode($value));
                }
            }
        }
    }

    /**
     *
     * @param $node
     * @return mixed
     * @throws \Exception
     */
    protected function createVarienObject($node)
    {
        $output = $this->commonXmlvarienFactory->create();
        switch ($node->nodeType) {


            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                return trim($node->textContent);
                break;

            case XML_ELEMENT_NODE:          

                
                $nodeVarienTagName = $this->varienFormat($node->tagName);
                $output->setData($nodeVarienTagName, $this->commonXmlvarienFactory->create());

                //$output->getData($nodeVarienTagName)->addData(array('_nodetype' => 'XML_ELEMENT_NODE'));
                // loop through the attributes and collect them
                $attributes = $this->commonXmlvarienFactory->create();
                foreach ($node->attributes as $attrName => $attrNode) {
                    $attributes->setData($this->varienFormat($attrName), $attrNode->value);
                }
                if ($attributes->hasData())
                    $output->getData($nodeVarienTagName)->addData(array('_attributes' => $attributes));

                // for each child node, call the covert function recursively
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $tag_name = @$child->tagName;
                    if ($child->nodeType != XML_COMMENT_NODE) {
                        
                        $child_data = $this->createVarienObject($child);

                        if ($child_data instanceof \Epicor\Common\Model\Xmlvarien) {

                            $childXmlObj = $child_data->getData($this->varienFormat($tag_name));

                            $attributes = array();

                            if ($childXmlObj instanceof \Epicor\Common\Model\Xmlvarien) {
                                $attributes = $childXmlObj->getData('_attributes');
                            }

                            if (empty($attributes) && $child->attributes->length > 0) {
                                $newChildData = $this->commonXmlvarienFactory->create([
                                    'data' => array(
                                        'value' => $childXmlObj,
                                        '_attributes' => $this->commonXmlvarienFactory->create()
                                    )
                                ]);

                                foreach ($child->attributes as $attrName => $attrNode) {
                                    $newChildData->getData('_attributes')->setData($this->varienFormat($attrName), $attrNode->value);
                                }

                                $child_data->setData($this->varienFormat($tag_name), $newChildData);
                            }
                        }

                        if (empty($tag_name)) {
                            $output->setData($nodeVarienTagName, $child_data);
                        } else {
                            $keys = array_keys($child_data->getData());
                            if (is_a($output->getData($nodeVarienTagName), 'Magento\Framework\DataObject') && $output->getData($nodeVarienTagName)->getData($keys[0]) !== null) {
                                if (is_array($output->getData($nodeVarienTagName)->getData($keys[0])))
                                    $orig_array = $output->getData($nodeVarienTagName)->getData($keys[0]);
                                else
                                    $orig_array = array($output->getData($nodeVarienTagName)->getData($keys[0]));

                                $output->getData($nodeVarienTagName)->setData($this->varienFormat($keys[0]), array_merge($orig_array, array($child_data->getData($keys[0]))));
                            }
                            elseif (is_a($output->getData($nodeVarienTagName), 'Magento\Framework\DataObject')) {
                                $output->getData($nodeVarienTagName)->addData($child_data->getData());
                            } else {
                                throw new \Exception('Invalid XML at ' . $tag_name . ' within ' . $node->tagName);
                            }
                        }
                    }
                }
                if (is_a($output->getData($nodeVarienTagName), 'Magento\Framework\DataObject') && !$output->getData($nodeVarienTagName)->hasData())
                    $output->setData($nodeVarienTagName, null);

                break;

            default:
                return '';
        }

        return $output;
    }

    private function varienFormat($key)
    {
        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $key));
    }

    private function xmlFormat($key)
    {
        return preg_replace_callback(
            '/([a-z0-9])_([a-z])/', function($matches) {
            return $matches[1] . strtoupper($matches[2]);
        }, $key
        );
    }

}
