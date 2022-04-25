<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details;


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
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/deid/info.phtml');
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
        return $this->_infoData;
    }
    
    
    public function getBasicLocationDetails()
    {
        return $this->_infoBasicData;
    } 
    
    public function getLocAddress()
    {
        return $this->_infoLocationAddress;
    }   
    
    public function getSoldToAddress()
    {
        return $this->_infoSoldToAddress;
    } 
    
    public function getOwnerAddress()
    {
        return $this->_infoOwnerAddress;
    }     
    
    
    //M1 > M2 Translation End

}