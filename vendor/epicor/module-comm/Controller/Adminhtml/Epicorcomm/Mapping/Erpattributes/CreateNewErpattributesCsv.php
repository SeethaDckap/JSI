<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class CreateNewErpattributesCsv extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Erp\Mapping\AttributesFactory
     */
    protected $commResourceErpMappingAttributesFactory;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\AttributesFactory $commResourceErpMappingAttributesFactory
    ) {
        $this->commHelper = $commHelper;
        $this->commResourceErpMappingAttributesFactory = $commResourceErpMappingAttributesFactory;
    }
    /**
     * Generates a CSV that can be used for create a new list
     *
     * @return void
     */
    public function execute()
    {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename= example_create_new_attribute.csv");
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies   
        $attributeTypes = implode("; ", array_keys($this->commHelper->_getEccattributeTypes(false)));

        $tableColumns = array_flip(array_keys($this->commResourceErpMappingAttributesFactory->create()->getFields()));
        unset($tableColumns['id']);
        $tableColumns = implode(",", array_flip($tableColumns));
        $header = '### Attribute Code             : Alphanumeric' . "\n" .
            '### Input Type                 : ' . $attributeTypes . "\n" .
            '### Separator                  : When uploading multiple options. Only used if input type is multiselect' . "\n" .
            '### Use For Config             : When uploading configurable products. Only used if input type is select (ie dropdown)' . "\n" .
            '### Quick Search               : Y/N' . "\n" .
            '### Advanced Search            : Y/N' . "\n" .
            '### Search Weighting           : Numeric' . "\n" .
            '### Use In Layered Navigation  :  0:null; 1:filterable(with results; 2:filterable(no results)' . "\n" .
            '### Search Results             : Y/N' . "\n" .
            '### Visible On Product View    : Y/N' . "\n" .
            '###' . "\n";
        ;
        $csvString = $header . $tableColumns;

        $this->getResponse()->setBody($csvString);
    }

    }
