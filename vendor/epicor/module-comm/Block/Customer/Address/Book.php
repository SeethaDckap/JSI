<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Block\Customer\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;

class Book extends \Magento\Customer\Block\Address\Book
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Book constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        AddressCollectionFactory $addressCollectionFactory,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->formKey = $formKey;
        $this->productMetadata = $productMetadata;
        $this->addressCollectionFactory = $addressCollectionFactory;
        parent::__construct(
            $context,
            $customerRepository,
            $addressRepository,
            $currentCustomer,
            $addressConfig,
            $addressMapper,
            $data
        );
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }
    
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * validate address book
     * Grid magento Compatible  >= 2.3.1
     *
     * @return bool
     */
    public function isGridCompatible()
    {
        if ($this->productMetadata->getVersion() > '2.3.0') {
            return true;
        }
        return false;
    }

    /**
     * @return array|bool|\Magento\Customer\Api\Data\AddressInterface[]|\Magento\Customer\Model\Address[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdditionalAddresses()
    {
        $addresses = [];
        $customer = $this->getCustomerSession()->getCustomer();
        $erpAccounts = $customer->getErpAcctCounts();
        if (count($erpAccounts) > 1) {
            $collection = $this->addressCollectionFactory->create();
            $collection->addAttributeToSelect('ecc_erp_address_code');
            $collection->setOrder('entity_id', 'desc')
                ->setCustomerFilter([$customer->getId()]);
            $defBillingCode = $customer->getDefaultBillingAddress()->getData("ecc_erp_address_code");
            $defShippingCode = $customer->getDefaultShippingAddress()->getData("ecc_erp_address_code");
            $primaryAddressIds = [$defBillingCode, $defShippingCode];
            $erpInfo = $customer->getCustomerErpAccount();
            $erpCustomerGroupCode = $erpInfo->getData("erp_code");
            $attributes = [
                ['attribute' => 'ecc_erp_group_code', 'eq' => $erpCustomerGroupCode],
                ['attribute' => 'ecc_erp_group_code', 'null' => true],
            ];
            $collection->addAttributeToFilter($attributes, null, 'left');
            foreach ($collection as $address) {
                if (!in_array($address->getData("ecc_erp_address_code"), $primaryAddressIds)) {
                    $addresses[] = $address;
                }
            }
        } else {
            $addresses = $customer->getAdditionalAddresses();
        }
        return $addresses;
    }
}