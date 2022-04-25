<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


class Xml extends \Epicor\Common\Helper\Data
{

    /**
     * @var \Epicor\Common\Model\Converter\XmltoarrayFactory
     */
    protected $commonConverterXmltoarrayFactory;

    /**
     * @var \Epicor\Common\Model\Converter\XmltovarienFactory
     */
    protected $commonConverterXmltovarienFactory;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\Common\Model\Converter\XmltoarrayFactory $commonConverterXmltoarrayFactory,
        \Epicor\Common\Model\Converter\XmltovarienFactory $commonConverterXmltovarienFactory
    ) {
        $this->commonConverterXmltoarrayFactory = $commonConverterXmltoarrayFactory;
        $this->commonConverterXmltovarienFactory = $commonConverterXmltovarienFactory;
        parent::__construct($context);
    }
    /**
     * Convert xml String to Array
     * 
     * @param string $xml
     * @return array
     */
    public function convertXmlToArray($xml)
    {

        $convert = $this->commonConverterXmltoarrayFactory->create();
        /* @var $convert Epicor_Common_Model_Converter_Xmltoarray */
        $array = $convert->xml2Array($xml);

        return $array;
    }

    /**
     * Convert Array to xml String
     * 
     * @param array $array
     * @return string
     */
    public function convertArrayToXml($array)
    {

        $convert = $this->commonConverterXmltoarrayFactory->create();
        /* @var $convert Epicor_Common_Model_Converter_Xmltoarray */
        $xml = $convert->array2Xml($array);

        return $xml;
    }

    /**
     * Convert xml String to Varien Object
     * 
     * @param string $xml
     * @return \Magento\Framework\DataObject
     */
    public function convertXmlToVarienObject($xml)
    {
        $v_obj = false;
        if (!empty($xml)) {
            $convert = $this->commonConverterXmltovarienFactory->create();
            $v_obj = $convert->xml2Varien($xml);
        }
        return $v_obj;
    }

    /**
     * Convert xml String to Array
     * 
     * @param string $xml
     * @return array
     */
    public function convertXmlToArraynew($xml)
    {
        $v_obj = false;
        if (!empty($xml)) {
            $convert = $this->commonConverterXmltovarienFactory->create();
            $v_obj = $convert->xml2Array($xml);
        }
        return $v_obj;
    }

    /**
     * Convert Varien Object to xml String
     * 
     * @param \Magento\Framework\DataObject $v_obj
     * @return string
     */
    public function convertVarienObjectToXml($v_obj)
    {

        $convert = $this->commonConverterXmltovarienFactory->create();
        /* @var $convert \Epicor\Common\Model\Converter\Xmltovarien */
        $xml = $convert->varien2Xml($v_obj);

        return $xml;
    }

    public function convertXmlToHtml($xml)
    {

        $convert = $this->commonConverterXmltovarienFactory->create();
        /* @var $convert \Epicor\Common\Model\Converter\Xmltovarien */
        $html = $convert->xmlToHtml($xml);

        return $html;
    }

}
