<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Eav\Attribute\Data;


/**
 * Branchpickup 
 *
 * @category   Epicor
 * @package    Epicor_Branchpickup
 * @author     Epicor Websales Team
 */
class LoginModeTypeOptions extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    
     /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;
    
   protected $commonHelper;    
    

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Common\Helper\Data $commonHelper
        
    ) {
        $this->_request = $request;
        $this->customerCustomerFactory = $customerCustomerFactory; 
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $eavAttrEntity
        );
    }


    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
           $customerId =  (int)$this->_request->getParam('id'); 
            $customer = $this->customerCustomerFactory->create()->load($customerId);
            $customerType = $customer->getEccErpAccountType();
           // $customer->getEccLoginModeType();
            $typeLabel = ($customerType == "guest") ? "Global Default" : "ERP Account Default";
            $this->_options = array(
                array(
                    'label' => $typeLabel,
                    'value' => 2
                ),array(
                    'label' => __('Dealer'),
                    'value' => 'dealer'
                ),array(
                    'label' => __('End Customer'),
                    'value' => 'shopper'
                )
            );
            $helper = $this->commonHelper;
            $dealerLicense = $helper->checkDealerLicense();       
            //If thee is no dealer License then remove that
            if(!$dealerLicense) {
                unset($this->_options[1]);
            }         

        }
        return $this->_options;
    }

}
