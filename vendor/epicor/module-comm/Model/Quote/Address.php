<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Quote;


use Epicor\Comm\Model\MinOrderAmountFlag;

class Address extends \Magento\Quote\Model\Quote\Address
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    private $minOrderAmountFlag;

    public function __construct(
        MinOrderAmountFlag $minOrderAmountFlag,
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\ItemFactory $addressItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory,
        \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory,
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Quote\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory,
        \Magento\Quote\Model\Quote\Address\TotalFactory $addressTotalFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \Magento\Quote\Model\Quote\Address\Validator $validator,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface $attributeList,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->minOrderAmountFlag = $minOrderAmountFlag;
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
            $scopeConfig,
            $addressItemFactory,
            $itemCollectionFactory,
            $addressRateFactory,
            $rateCollector,
            $rateCollectionFactory,
            $rateRequestFactory,
            $totalCollectorFactory,
            $addressTotalFactory,
            $objectCopyService,
            $carrierFactory,
            $validator,
            $addressMapper,
            $attributeList,
            $totalsCollector,
            $totalsReader,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Validate minimum amount
     *
     * @return bool
     */
    //M1 > M2 Translation Begin (Rule 26)
    //public function _basicCheck()
    //{   // this is now performed in observer postValidateAddress
    //    return;
    //}
    //M1 > M2 Translation End

    public function validateMinimumAmount()
    {

        if($this->getQuote()->getArpaymentsQuote()) {
            return true;
        }

        if (!$this->minOrderAmountFlag->isMinOrderActive($this->getQuote()->getData('ecc_erp_account_id'))) {
            return true;
        }

        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        } elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != self::TYPE_SHIPPING) {
            return true;
        }

        $customerData = $this->getQuote()->getCustomer();
        $erpAccountIdAtt = $customerData->getCustomAttribute('ecc_erpaccount_id');
        $erpAccountId = ($erpAccountIdAtt) ? $erpAccountIdAtt->getValue() : null;

        $amount = $this->minOrderAmountFlag->getMinOrderValueAmount($erpAccountId);
        if ($this->getGrandTotal() < $amount) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve collection of quote shipping rates filtered by ERP accounts
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getShippingRatesCollection()
    {
        parent::getShippingRatesCollection();

        $validShippingMethods = array();
        $invalidShippingMethods = array();
        $allowedTypes = array();
        $erpAccountId = $this->commHelper->getErpAccountId();

        if ($erpAccountId) {
            $erpGroup = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
            $getAccountType = $erpGroup->getAccountType();
            $allowedTypes = array("B2B", "B2C", "Dealer");
            if (in_array($getAccountType, $allowedTypes)) {
                if (!(is_null($erpGroup->getAllowedDeliveryMethods()) &&
                    is_null($erpGroup->getAllowedDeliveryMethodsExclude()))) {

                    $exclude = !is_null($erpGroup->getAllowedDeliveryMethods()) ? 'N' : 'Y';
                    $validShippingMethods = unserialize($erpGroup->getAllowedDeliveryMethods());
                    $invalidShippingMethods = unserialize($erpGroup->getAllowedDeliveryMethodsExclude());
                    $removeRates = array();
                    foreach ($this->_rates as $key => $rate) {
                        if ($exclude == 'N') {
                            if (!in_array($rate->getCode(), $validShippingMethods)) {
                                $removeRates[] = $key;
                            }
                        } else {
                            if (in_array($rate->getCode(), $invalidShippingMethods)) {
                                $removeRates[] = $key;
                            }
                        }
                    }

                    foreach ($removeRates as $key) {
                        $this->_rates->removeItemByKey($key);
                    }
                }
            }
        }
        return $this->_rates;
    }

}
