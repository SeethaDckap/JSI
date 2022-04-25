<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request - CNC Create New Customer
 * This message instructs the ERP to create a new ERP customer. 
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setAccount(\Epicor\Comm\Model\Customer\Erpaccount $account)
 * @method \Epicor\Comm\Model\Customer\Erpaccount getAccount()
 */
class Cnc extends \Epicor\Comm\Model\Message\Request
{

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('CNC');
        $this->setLicenseType('Customer');
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->_msg_parent = parent::MESSAGE_TYPE_REQUEST;
        $this->setConfigBase('epicor_comm_enabled_messages/cnc_request/');

    }

    /**
     * Create a CNC request
     *
     * @return boolean
     */
    public function buildRequest()
    {
        $helper = $this->getHelper();

        $data = $this->getMessageTemplate();

        $account = $this->getAccount();
        /* @var $account \Epicor\Comm\Model\Customer\Erpaccount */

        $this->setMessageSecondarySubject($account->getName());
        
        $customer = array(
            '_attributes' => array(
                       'type' => ($account->getAccountType()) ? $account->getAccountType() : 'B'
                   ),
            'name' => $account->getName(),
            'templateCode' => $this->getConfig($account->getTemplateCodePath()),
        );

        $addresses = $account->getAddresses();

        foreach ($addresses as $address) {

            if ($address->getIsRegistered()) {
                $key = 'registeredAddress';
            } else if ($address->getIsDelivery()) {
                $key = 'deliveryAddress';
            } else if ($address->getIsInvoice()) {
                $key = 'invoiceAddress';
            }

            if ($address->getCountyId()) {
                $region = $this->directoryRegionFactory->create()->load($address->getCountyId());
                $address->setCounty($helper->getRegionNameOrCode($region->getCountryId(), $region->getCode()));
            }

            $customer[$key] = array(
                'name' => $helper->stripNonPrintableChars($address->getName()),
                //M1 > M2 Translation Begin (Rule 9)
                //'address1' => $helper->stripNonPrintableChars($address->getAddress1()),
                //'address2' => $helper->stripNonPrintableChars($address->getAddress2()),
                //'address3' => $helper->stripNonPrintableChars($address->getAddress3()),
                'address1' => $helper->stripNonPrintableChars($address->getData('address1')),
                'address2' => $helper->stripNonPrintableChars($address->getData('address2')),
                'address3' => $helper->stripNonPrintableChars($address->getData('address3')),
                //M1 > M2 Translation End
                'city' => $helper->stripNonPrintableChars($address->getCity()),
                'county' => $helper->stripNonPrintableChars($address->getCounty()),
                'country' => $helper->getCountryCodeMapping($helper->getErpCountryCode($address->getCountry())),
                'postcode' => $helper->stripNonPrintableChars($address->getPostcode()),
                'telephoneNumber' => $helper->stripNonPrintableChars($address->getPhone()),
                'mobileNumber' => $helper->stripNonPrintableChars($address->getMobileNumber()),
                'faxNumber' => $helper->stripNonPrintableChars($address->getFaxNumber()),
                'emailAddress' => $helper->stripNonPrintableChars($address->getEmailAddress()),
            );

            if ($key == 'deliveryAddress') {
                $customer[$key]['carriageText'] = $helper->stripNonPrintableChars($address->getInstructions());
            }
        }

        $customer['registrationEmailAddress'] = $account->getEmail();

        $data['messages']['request']['body']['customer'] = $customer;

        $this->setOutXml($data);

        return true;
    }

    /**
     * Processes the CNC response
     * 
     * @return boolean
     */
    public function processResponse()
    {
        $success = false;

        if ($this->getIsSuccessful()) {

            $helper = $this->commMessagingCustomerHelper;
            /* @var $helper \Epicor\Comm\Helper\Messaging\Customer */
            $customer = $this->getResponse()->getCustomer();

            $accountCode = $this->getVarienData('customer_account_code', $customer);
            $name = $this->getVarienData('customer_account_name', $customer);
            $this->setMessageSubject($accountCode);
            $this->setMessageSecondarySubject($name);

            if (!$customer->getAccountId()) {
                $customer->setAccountId($customer->getAccountNumber());
            }

            $customer->setOrigAccountNumber($customer->getAccountNumber());

            $helper = $this->commMessagingCustomerHelper;

            $brands = $customer->getBrands();
            $brand = null;
            if (!is_null($brands)) {
                $brand = $brands->getBrand();
            }

            if (is_array($brand)) {
                $brand = $brand[0];
            }

            if (empty($brand) || !$brand->getCompany()) {
                //M1 > M2 Translation Begin (Rule p2-6.5)
                //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
                $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
                //M1 > M2 Translation End
            }

            $company = $brand->getCompany();

            $customer->setBrandCompany($company);

            if (!empty($company)) {
                $delimiter = $helper->getUOMSeparator();
                $customer->setAccountNumber($company . $delimiter . $customer->getAccountNumber());
                $this->getLog()->setMessageSubject($customer->getAccountNumber())->save();
            }

            $erpCustomer = $helper->processCustomerAction($customer, 'epicor_comm_field_mapping/cus_mapping/');
            $this->setAccount($erpCustomer);
            $success = true;
        }

        return $success;
    }

}
