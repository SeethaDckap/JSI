<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Contract;


/**
 * Contract Settings Default Configurations
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class DefaultBlock extends \Magento\Framework\View\Element\Template
{

    /**
     *
     * @var \Epicor\Comm\Model\Customer\Erpaccount 
     */
    private $_erpAccount;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Lists\Helper\Frontend
     */
    protected $listsFrontendHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Lists\Helper\Frontend $listsFrontendHelper,
        array $data = []
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->customerSession = $customerSession;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->listsHelper = $listsHelper;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->listsFrontendHelper = $listsFrontendHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Default Contract'));
    }

    /**
     * Get Contract Helper
     * @return \Epicor\Lists\Helper\Frontend\Contract
     */
    public function getContractHelper()
    {
        if (!$this->_contractHelper) {
            $this->_contractHelper = $this->listsFrontendContractHelper;
        }
        return $this->_contractHelper;
    }

    public function isAllowed()
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $customer = $customerSession->getCustomer();
        $allowed = false;
        if ($customer->getId()) {

            $allowed = true;
        }
        return $allowed;
    }

    public function getFormUrl()
    {
        return $this->getUrl('epicor_lists/contract/save');
    }

    public function getReturnUrl()
    {
        $url = $this->frameworkHelperDataHelper->getCurrentUrl();
        return $this->listsHelper->urlEncode($url);
    }

    public function getAjaxAddressUrl()
    {
        return $this->getUrl('lists/contract/getcontractaddress/');
    }

    /**
     * Get session customer allowed contracts
     * 
     * @return array
     */
    public function getCustomerAllowedContracts()
    {
        $contracts = $this->getContractHelper()->getActiveContracts();
        $customerData = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
        $defaultContract = $customerData->getEccDefaultContract();
        if (!is_array($contracts)) {
            $select = array();
        } else {
            $select = '<select name="contract_default" id="contract_default" class="select absolute-advice">';
            $select .= '<option value="">No Default Contract</option>';
            foreach ($contracts as $code => $contractvals) {
                $defaultSelect = ($code == $defaultContract ? "selected=selected" : "");
                $select .= '<option value="' . $code . '" ' . $defaultSelect . '>' . $contractvals->getTitle() . '</option>';
            }
            $select .= '</select>';
        }
        return $select;
    }

    /**
     * Get Selected Customer Address for the particular Contract
     * 
     * @return array
     */
    public function getCustomerSelectedAddress($contractId)
    {
        $loadHelper = $this->listsFrontendHelper->customerAddresses($contractId);
        $customerData = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
        $defaultContractAddress = $customerData->getEccDefaultContractAddress();
        $select['type'] = 'success';
        $select['html'] = '<select name="contract_default_address" id="contract_default_address" class="select absolute-advice">';
        $select['html'] .= '<option value="">No Default Address</option>';
        foreach ($loadHelper as $code => $address) {
            $defaultSelect = ($code == $defaultContractAddress ? "selected=selected" : "");
            $select['html'] .= '<option value="' . $code . '" ' . $defaultSelect . '>' . $address->getName() . '</option>';
        }
        $select['html'] .= '</select>';
        return $select;
    }

}
