<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Inventory;
/**
 * 
 * 
 * @category    Epicor
 * @package     Epicor_Dealerconnect
 * @author      Epicor Web Sales Team
 */

class Pac extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory;
    protected $jsonHelper;
    
    
    protected $_eccPacAttributeFactory;  
    
    
    protected $_eccPacFactory;      

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Epicor\Dealerconnect\Model\EccPacFactory $eccPacFactory,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory $eccPacAttributes
    ) {
        $this->_eccPacFactory = $eccPacFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_eccPacAttributeFactory = $eccPacAttributes;
        
        
        parent::__construct($context);
    }

    
    public function execute()
    {
        $selectedVals = $this->getRequest()->getParam('selectedVals');
        $collectionVales = $this->getCategoryCollection($selectedVals);
        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($collectionVales));
    }
    
    
    public function getCategoryCollection($selectedVals)
    {   
        $explodeVals = explode('_',$selectedVals);
        $attributeId = $explodeVals[1];
        
        
        $collection = $this->_eccPacFactory->create()->getCollection();
        $collection->getSelect()->join(array('cs' => $collection->getTable('ecc_pac_attributes')),"cs.class_id = main_table.entity_id",array('classId' => 'cs.entity_id','cs.datatype','cs.label','cs.attribute_id','cs.erp_searchable','cs.description'));
        $collection->addFieldToFilter('cs.entity_id', $attributeId);
        //$collection = $this->_eccPacAttributeFactory->create();
        //$collection->addFieldToFilter('entity_id', $attributeId);
        $collectionData = $collection->getData();
        foreach ($collectionData as $key => $dataResult) {
            $collectionResult['datatype'] = strtolower($dataResult['datatype']);
            $collectionResult['label'] = $dataResult['label'];
            $collectionResult['parentclass'] = $dataResult['attribute_class_id'];
            $collectionResult['parentclassdescription'] = $dataResult['description'];
            $collectionResult['attributeDescription'] = $dataResult['description'];
            $collectionResult['classId'] = $attributeId;
            $collectionResult['pacattribute'] = $selectedVals;
            $collectionResult['pacattributeName'] = $dataResult['attribute_id'];
            $collectionResult['pacattributeid'] = $selectedVals;
            $collectionResult['erpsearchable'] = $dataResult['erp_searchable'];
        }
        return $collectionResult;
    }    
    
}