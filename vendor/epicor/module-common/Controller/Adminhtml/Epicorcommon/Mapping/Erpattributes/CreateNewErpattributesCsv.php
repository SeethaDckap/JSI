<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes;

class CreateNewErpattributesCsv extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes
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
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\AttributesFactory $commResourceErpMappingAttributesFactory
    )
    {
        $this->commHelper = $commHelper;
        $this->commResourceErpMappingAttributesFactory = $commResourceErpMappingAttributesFactory;
        parent::__construct($context, $backendAuthSession);
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
        $header = '### Attribute Code                                               : Alphanumeric' . "\n" .
            '### Input Type                                                         : ' . $attributeTypes . "\n" .
            '### Separator                                                          : When uploading multiple options. Only used if input type is multiselect' . "\n" .
            '### Use In Search (is_searchable)                                      : Y/N' . "\n" .
            '### Search Weight (1-10)                                               : Numeric' . "\n" .
            '### Visible in Advanced Search(is_visible_in_advanced_search)          : Y/N' . "\n" .
            '### Comparable on Storefront (is_comparable)                           : Y/N' . "\n" .
            '### Use In Layered Navigation (is_filterable)                          : 0:null; 1:filterable(with results; 2:filterable(no results)' . "\n" .
            '### Use in Search Results Layered Navigation (is_filterable_in_search) : Y/N' . "\n" .
            '### Position                                                           : Numeric' . "\n" .
            '### Use for Promo Rule Conditions (is_used_for_promo_rules)            : Y/N' . "\n" .
            '### Allow Html Tags on Storefront (is_html_allowed_on_front)           : Y/N' . "\n" .
            '### Visible on Catalog Pages on Storefront(is_visible_on_front)        : Y/N' . "\n" .
            '### Used in Product Listing                                            : Y/N' . "\n" .
            '### Used for Sorting in Product Listing (used_for_sort_by)             : Y/N' . "\n" .
            '###' . "\n";
        ;
        $csvString = $header . $tableColumns;

        $this->getResponse()->setBody($csvString);
    }

    }
