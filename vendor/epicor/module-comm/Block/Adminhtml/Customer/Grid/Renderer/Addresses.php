<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Grid\Renderer;


/**
 * Customer Account Type Grid Renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Addresses extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Helper\MessagingFactory
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Helper\MessagingFactory $commMessagingHelper,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        array $data = []
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->directoryCountryFactory = $directoryCountryFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commMessagingHelper->create();
        /* @var $helper Epicor_Common_Helper_Account_Selector */
        $index = $this->getColumn()->getIndex();
        $entity_id = $row->getData('entity_id');
        $accountId = $row->getData('erp_link');
        $customer = $this->customerCustomerFactory->create()->load($entity_id);
        $erpCount = $customer->getErpAcctCounts();
        if(!empty($erpCount) && count($erpCount) > 1){
            $erpAccountInfo = $helper->getErpAccountInfo($accountId);
            $defaultBillingAddressCode = $erpAccountInfo->getDefaultInvoiceAddressCode();
            $billingAddress = $erpAccountInfo->getAddress($defaultBillingAddressCode);
            switch ($index) {
                case 'billing_postcode':
                    return $helper->stripNonPrintableChars($billingAddress->getData('postcode'));
                    break;
                case 'billing_country_id':
                    $countryModel = $this->directoryCountryFactory->create()->loadByCode($billingAddress->getData('country'));
                    return $countryModel->getName();
                    break;
                case 'billing_region':
                    return $helper->stripNonPrintableChars($billingAddress->getData('county'));
                    break;
                case 'billing_telephone':
                    return $helper->stripNonPrintableChars($billingAddress->getData('phone'));
                    break;
                default:
                    return $row->getData($index);
            }
        }else{
            switch ($index) {
                case 'billing_country_id':
                    if($row->getData($index)){
                        $countryModel = $this->directoryCountryFactory->create()->loadByCode($row->getData($index));
                        return $countryModel->getName();
                    }
                    return $row->getData($index);
                    break;
                default:
                    return $row->getData($index);
            }
        }
    }

}
