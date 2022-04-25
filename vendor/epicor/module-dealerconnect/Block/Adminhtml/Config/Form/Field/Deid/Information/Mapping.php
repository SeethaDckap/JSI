<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deid\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{


    protected $_eccPacFactory;

    protected $messageTypes ="DEID";

    protected $paramsOptions = array('data-pac'=>'');

    protected $gridMappingHelper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Dealerconnect\Model\EccPacFactory $eccPacFactory,
        \Epicor\Common\Helper\GridMapping $gridMappingHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $gridMappingHelper,
            $data
        );
        $this->gridMappingHelper = $gridMappingHelper;
        $this->_eccPacFactory = $eccPacFactory;
    }


    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:50%"');
    }

    public function getCategoryCollection()
    {
        $collection = $this->_eccPacFactory->create()->getCollection();
        //$collection->getSelect()->join(array('at' => $collection->getTable('ecc_pac_attribute')),'at.entity_id',array('attributeName' => 'at.name','parentId' => 'at.entity_id'));
        $collection->getSelect()->join(array('cs' => $collection->getTable('ecc_pac_attributes')),"cs.class_id = main_table.entity_id",array('classId' => 'cs.entity_id','cs.datatype','cs.label','attributeId' => 'cs.attribute_id'));
        $collection->addFieldToFilter('erp_searchable', array('eq' => 'Y'));
        $collectionResult = $collection;
        return $collectionResult;
    }


    public function getPacOptions()
    {

        $pacValues = $this->getCategoryCollection();
        $arrayPacVals  = array();
        foreach($pacValues->getData() as $getPacVals) {
            $vals = json_encode($getPacVals);
            $arrayPacVals[$getPacVals['classId']] = array('selectVals'=> $getPacVals['entity_id']. '_'. $getPacVals['attribute_class_id'].' -> '.$getPacVals['label'],'params'=>$vals );
        }
        return $arrayPacVals;
    }


    public function _toHtml()
    {

        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!$this->getOptions()) {
            $params = array('data-pac'=>'');
            $this->addOption('identification_number', __('identificationNumber'),$params);
            $this->addOption('serial_number', __('serialNumber'),$params);
            $this->addOption('product_code', __('productCode'),$params);
            $this->addOption('description', __('description'),$params);
            $this->addOption('order_number', __('orderNumber'),$params);
            $this->addOption('listing', __('listing'),$params);
            $this->addOption('listing_date', __('listingDate'),$params);
            $this->addOption('product_code', __('productCode'),$params);
            $this->addOption('warranty_code', __('warrantyCode'),$params);
            $this->addOption('warranty_comment', __('warrantyComment'),$params);
            $this->addOption('warranty_start_date', __('warrantyStartDate'),$params);
            $this->addOption('warranty_expiration_date', __('warrantyExpirationDate'),$params);
            $this->addOption('inventory_status', __('inventoryStatus'),$params);
        }
        return parent::_toHtml();
    }

}