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
class InfoDetails extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_infoData = array();
    
    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_infoDataCheck = array();
    
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

    protected $warrInfo = array('warranty_code', 'warranty_comment', 'warranty_expiration', 'warranty_start');
    
    protected $dWarrInfo = array('dealer_warranty_code', 'dealer_warranty_comment', 'dealer_warranty_expiration', 'dealer_warranty_start');

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
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/debm/info.phtml');
        $this->setColumnCount(3);
    }

    /**
     * 
     * @return \Epicor\Customerconnect\Helper\Data
     */
    public function getHelper($type = null)
    {
        //M1 > M2 Translation Begin (Rule p2-7)
        //return Mage::helper('customerconnect');
        return $this->customerconnectHelper;
        //M1 > M2 Translation End
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getInfoData()
    {
        $arrFilter = array_keys(array_filter($this->_infoDataCheck, function($value) { return ($value == false && $value !== 0); }));
        if(count(array_intersect($this->warrInfo, $arrFilter)) == count($this->warrInfo)){
            $newArr = array_diff_key($this->_infoData, array_flip($this->warrInfo));
        }else{
            $newArr = $this->_infoData;
        }
        if(count(array_intersect($this->dWarrInfo, $arrFilter)) == count($this->dWarrInfo)){
            $newArr = array_diff_key($newArr, array_flip($this->dWarrInfo));
        }
        return $newArr;
    }
}