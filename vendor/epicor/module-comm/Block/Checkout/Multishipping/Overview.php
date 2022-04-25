<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Checkout\Multishipping;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;

/**
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Overview extends \Magento\Multishipping\Block\Checkout\Overview {

    public $commHelper;
    public $scopeConfig;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping, \Magento\Tax\Helper\Data $taxHelper, PriceCurrencyInterface $priceCurrency, \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector, \Magento\Quote\Model\Quote\TotalsReader $totalsReader, \Epicor\Comm\Helper\Data $commHelper, $data = []
    ) {
        parent::__construct($context, $multishipping, $taxHelper, $priceCurrency, $totalsCollector, $totalsReader);
        $this->commHelper = $commHelper;
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function getShippingAddressTotals($address) {
        $totals = $address->getTotals();

        if ($this->commHelper->removeTaxLine($totals['tax']->getValue())) {
            unset($totals['tax']);
        }
        foreach ($totals as $key => $total) {

            if ($total->getTitle() == 'BSV') {
                unset($totals[$key]);
            }

            if ($total->getCode() == 'grand_total') {
                if ($address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_BILLING) {
                    $total->setTitle(__('Total'));
                } else {
                    $total->setTitle(__('Total for this address'));
                }
            }
        }

        return $totals;
    }

    /**
     * Retrieve renderer block for row-level item output
     *
     * @param string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _getRowItemRenderer($type) {
        $renderer = $this->getItemRenderer($type);
        if ($renderer !== $this->getItemRenderer(self::DEFAULT_TYPE)) {
            $renderer->setTemplate('Epicor_Comm::epicor_comm/checkout/multishipping/overview/item.phtml');
        }
        return $renderer;
    }

}
