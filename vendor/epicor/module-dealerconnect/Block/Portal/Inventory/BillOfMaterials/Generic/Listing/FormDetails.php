<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method int getColumnCount()
 * @method void setColumnCount(int $count)
 * @method bool getOnRight()
 * @method void setOnRight(bool $bool)
 */
class FormDetails extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_infoData = array();
    
    protected $_infoBasicData;
    
    protected $_infoLocationAddress;
    
    protected $_infoSoldToAddress;
    
    protected $_infoOwnerAddress;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;  
        
    protected $_url = '/dealerconnect/inventory/updatebillaction';

    protected $noEdit = array("material_id","product_code","description","assembly_seq","unit_of_measure_code","quantity");
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/dmau/form.phtml');
        $this->setColumnCount(3);
    }

    /**
     * 
     * @return \Epicor\Customerconnect\Helper\Data
     */
    public function getHelper($type = null)
    {
        return $this->customerconnectHelper;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        return $this->_infoData;
    }
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getEditableFields()
    {
        $infoData =  $this->_infoData;
        $noEdit = $this->noEdit;
        $canEdit = [];
        foreach ($infoData as $label => $value){
            if(!in_array($label, $noEdit)){
                if (strpos($label, '>') == false) {
                    array_push($canEdit, $label);
                }
            }
        }
        return $canEdit;
    }
    
    public function getUpdateUrl(){
        return $this->_url;
    }
    
}