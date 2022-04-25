<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Dmau\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{

    protected $_eccPacFactory;

    protected $messageTypes ="DMAU";

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
        $this->_eccPacFactory = $eccPacFactory;
        $this->gridMappingHelper = $gridMappingHelper;
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
            $this->addOption('material_id', __('materialID'),$params);
            $this->addOption('product_code', __('productCode'),$params);
            $this->addOption('description', __('description'),$params);
            $this->addOption('job_num', __('jobNum'),$params);
            $this->addOption('assembly_seq', __('assemblySeq'),$params);
            $this->addOption('unit_of_measure_code', __('unitOfMeasureCode'),$params);
            $this->addOption('serial_numbers_serial_number', __('serialNumbers > serialNumber'));
            $this->addOption('lot_numbers_lot_number', __('lotNumbers > lotNumber'));
            $this->addOption('quantity', __('quantity'),$params);
            $this->addOption('revision_num', __('revisionNum'),$params);
            $this->addOption('warranty_code', __('warrantyCode'),$params);
            $this->addOption('warranty_comment', __('warrantyComment'),$params);
            $this->addOption('warranty_start', __('warrantyStart'),$params);
            $this->addOption('warranty_expiration', __('warrantyExpiration'),$params);
        }
        return parent::_toHtml();
    }

}