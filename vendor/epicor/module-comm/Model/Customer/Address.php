<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Customer;

/**
 * Address abstract model
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Address extends \Magento\Customer\Model\Address
{

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Api\AddressMetadataInterface $metadataService,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $metadataService,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectHelper,
            $customerFactory,
            $dataProcessor,
            $indexerRegistry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Validate address attribute values
     *
     * @return bool
     */
    //M1 > M2 Translation Begin (Rule 26)
    //protected function _basicCheck()   // this is now performed in observer postValidateAddress, but needs to be here to stop the abstract method executing
    //{
    //    return;
    //}
    //M1 > M2 Translation End

    public function getCustomerAddressesCollection($addressId = null)
    {
        /* @var $customer Epicor_Comm_Model_Customer */
        $customer = $this->customerSessionFactory->create()->getCustomer();

        $addressCollection = $customer->getAddressCollection()
            ->setCustomerFilter($customer)
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('ecc_erp_address_code', 'left')
            ->addAttributeToFilter('ecc_is_delivery', 1)
            ->addExpressionAttributeToSelect('full_name', 'CONCAT({{firstname}}, " ", {{lastname}})', array('firstname', 'lastname'));

        return $addressCollection;
    }


    /**
     * To be used when processing _POST
     */
    public function implodeStreetAddress()
    {
        $this->setStreet($this->getData('street'));
        return $this;
    }
    
     /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     * @deprecated
     */
    
    public function save()
    {
        $isSalesRep = $this->getCustomer()->isSalesRep();
        if($isSalesRep){
                return $this;
        }else{
            parent::save();
        }
    } 

    /**
     * Get if this address is default shipping address.
     *
     * @return bool|null
     */
    public function isDefaultShipping()
    {
        return $this->_getData(\Magento\Customer\Api\Data\AddressInterface::DEFAULT_SHIPPING);
    }

    /**
     * Get if this address is default billing address
     *
     * @return bool|null
     */
    public function isDefaultBilling()
    {
        return $this->_getData(\Magento\Customer\Api\Data\AddressInterface::DEFAULT_BILLING);
    }
    
}
