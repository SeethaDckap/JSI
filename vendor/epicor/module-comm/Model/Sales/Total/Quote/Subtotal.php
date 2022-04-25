<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Sales\Total\Quote;


/**
 * 
 * Subtotal collector class, checks to see if bsv values can be used
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Subtotal extends \Magento\Tax\Model\Sales\Total\Quote\Subtotal
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $customerAddressRegionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory
        );
    }


    protected function _addSubtotalAmount(\Magento\Quote\Model\Quote\Address $address, $item)
    {
        $row_total_precision = $this->scopeConfig->getValue('checkout/options/row_total_precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 2;
        $row_total_tax_precision = $this->scopeConfig->getValue('checkout/options/row_total_tax_precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 2;

        $address->setBaseTotalAmount('subtotal', $address->getBaseTotalAmount('subtotal') + $this->storeManager->getStore()->roundPrice($item->getBaseRowTotal(), $row_total_precision));
        $address->setBaseSubtotalTotalInclTax($address->getBaseSubtotalTotalInclTax() + $this->storeManager->getStore()->roundPrice($item->getBaseRowTotalInclTax(), $row_total_tax_precision));

        $address->setTotalAmount('subtotal', $address->getTotalAmount('subtotal') + $this->storeManager->getStore()->roundPrice($item->getRowTotal(), $row_total_precision));
        $address->setSubtotalInclTax($address->getSubtotalInclTax() + $this->storeManager->getStore()->roundPrice($item->getRowTotalInclTax(), $row_total_tax_precision));

        return $this;
    }

}
