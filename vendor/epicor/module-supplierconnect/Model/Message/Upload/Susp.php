<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Upload;


/**
 * Response SUSP - Upload Supplier Record
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Susp extends \Epicor\Supplierconnect\Model\Message\Upload
{

    /**
     * @var \Epicor\B2b\Helper\Data
     */
    protected $b2bHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\B2b\Helper\Data $b2bHelper,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->b2bHelper = $b2bHelper;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        parent::__construct(
            $context,
            $resource,
            $resourceCollection,
            $data
        );
        $this->setConfigBase('supplierconnect_enabled_messages/SUSP_mapping/');
        $this->setMessageType('SUSP');
        $this->setLicenseType(array('Supplier'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_suppliererpaccount', true, true);
    }

    /**
     * Processes the SUSP request
     *
     * @throws \Exception - invalid account code
     */
    public function processAction()
    {
        $supplier = $this->getRequest()->getSupplier();
        if (!$supplier->getAccountId())
            $supplier->setAccountId($supplier->getAccountNumber());

        $accountId = $supplier->getAccountId();
        $origAccountNumber = $supplier->getAccountNumber();

        $brands = $this->getRequest()->getBrands();

        $brand = null;

        if (!is_null($brands)) {
            $brand = $brands->getBrand();
        } else {
            $brand = null;
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

        $supplier->setBrandCompany($company);

        if (!empty($company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $accountCode = $this->getVarienData('account_number', $supplier);
            $this->setVarienData('account_number', $supplier, $company . $delimiter . $accountCode);
        }

        $accountNumber = $this->getVarienData('account_number', $supplier);
        $accountName = $this->getVarienData('account_name', $supplier);
        $this->setMessageSubject($accountNumber);
        $this->setMessageSecondarySubject($accountName);

        if (!empty($accountNumber)) {

            $deleteFlag = $this->getVarienDataFlag('delete', $supplier);

            $erpCustomer = $this->getErpAccount($accountNumber, 'Supplier');
            /* @var $erpCustomer Epicor_Comm_Model_Customer_Erpaccount */

            if ($deleteFlag) {
                $this->deleteCustomer($erpCustomer);
            } else {

                $erpCustomer->setErpCode($accountNumber);

                $erpCustomer->setCompany($company);
                $erpCustomer->setAccountNumber($origAccountNumber);
                $erpCustomer->setShortCode($accountId);
                $erpCustomer->setName($accountName);
                $erpCustomer->setAccountType('Supplier');

                $this->b2bHelper->setPreregPassword($erpCustomer);

                $stores = array();

                $brandStores = $this->_loadStores($this->getRequest());
                foreach ($brandStores as $store) {
                    $stores[] = $store->getId();
                }

                $helper = $this->commonXmlHelper->create();

                $erpCustomer->setBrands(serialize($helper->varienToArray($brand->getBrand())));
                $erpCustomer->setBrandRefresh(false);
                $erpCustomer->setNewStores($stores);
                //$address_model = Mage::getModel('epicor_comm/customer_erpaccount_address');
                /* @var $address_model Epicor_Comm_Model_Customer_Erpaccount_Address */
                //$this->processAddress($supplier, $erpCustomer, $address_model::ADDRESS_REGISTERED, $address_model::ADDRESS_REGISTERED);
                //$this->processAddress($supplier, $erpCustomer, 'order', $address_model::ADDRESS_BILLING, true);
                //$this->processAddress($supplier, $erpCustomer, 'order', $address_model::ADDRESS_SHIPPING, true);
                $erpCustomer->save();
            }
        } else {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ACCOUNT_CODE), self::STATUS_INVALID_ACCOUNT_CODE);
        }
    }

    /**
     * Deletes a customer
     *
     * @param string $accountCode
     */
    private function deleteCustomer($erpCustomer)
    {
        if (!$erpCustomer->isObjectNew()) {
            $erpCustomer->delete();
        }
    }

    /**
     * Processes an address from the request against the provioded ERP account
     *
     * @param \Epicor\Comm\Model\Xmlvarien $supplier
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     * @param string $addressType
     * @param string $erpType
     * @param boolean $default
     *
     * @return \Epicor_Supplierconnect_Model_Message_Upload_Susp
     */
    private function processAddress($supplier, &$erpCustomer, $addressType, $erpType, $default = false)
    {
        $erpData = $this->getVarienData($addressType . '_address', $supplier);
        // process ERP address
        $helper = $this->commMessagingCustomerHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging_Customer */

        $address_code = $this->getVarienData($addressType . '_address_code', $erpData);
        $address_name = $this->getVarienData($addressType . '_address_name', $erpData);
        $address_line1 = $this->getVarienData($addressType . '_address_line1', $erpData);
        $address_line2 = $this->getVarienData($addressType . '_address_line2', $erpData);
        $address_line3 = $this->getVarienData($addressType . '_address_line3', $erpData);
        $address_city = $this->getVarienData($addressType . '_address_city', $erpData);
        $address_county = $this->getVarienData($addressType . '_address_county', $erpData);
        $address_postcode = $this->getVarienData($addressType . '_address_postcode', $erpData);
        $address_country = $helper->getCountryCodeMapping($this->getVarienData($addressType . '_address_country', $erpData));
        $address_email = $this->getVarienData($addressType . '_address_email', $erpData);
        $address_telephone = $this->getVarienData($addressType . '_address_telephone', $erpData);
        $address_fax = $this->getVarienData($addressType . '_address_fax', $erpData);

        $erpCustomer->addAddress($address_code, $erpType)
            ->setType($erpType, $address_code)
            ->setAddressName($address_name, $address_code)
            ->setAddress1($address_line1, $address_code)
            ->setAddress2($address_line2, $address_code)
            ->setAddress3($address_line3, $address_code)
            ->setCity($address_city, $address_code)
            ->setCounty($address_county, $address_code)
            ->setPostcode($address_postcode, $address_code)
            ->setCountry($address_country, $address_code)
            ->setEmail($address_email, $address_code)
            ->setPhone($address_telephone, $address_code)
            ->setFax($address_fax, $address_code);

        $errors = $erpCustomer->getAddress($address_code)->validate();

        if ($errors !== true && !empty($errors)) {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ADDRESS, ($addressType), implode(' ', $errors)), self::STATUS_INVALID_ADDRESS);
        }

        if ($default) {
            $erpCustomer->setData('default_' . $erpType . '_address_code', $address_code);
        }

        $this->eventManager->dispatch('epicor_message_cus_address_processed', array('erp_account' => $erpCustomer));
        return $this;
    }

}