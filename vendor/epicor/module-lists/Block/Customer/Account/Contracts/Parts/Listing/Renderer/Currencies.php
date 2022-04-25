<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\parts\Listing\Renderer;

/**
 * RFQ line attachments column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Currencies extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \Epicor\Lists\Helper\Messaging
     */
    protected $listsMessagingHelper;

    /**
      /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /*
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    protected $currency;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Epicor\Lists\Helper\Messaging $listsMessagingHelper, \Magento\Store\Model\StoreManagerInterface $storeManager, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Magento\Framework\Locale\ResolverInterface $localeResolver, \Magento\Directory\Model\CurrencyFactory $currency, array $data = []
    ) {
        $this->listsMessagingHelper = $listsMessagingHelper;
        $this->storeManager = $storeManager;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->_localeResolver = $localeResolver;
        $this->currency = $currency;
        parent::__construct(
                $context, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {
        $helper = $this->listsMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $currencies = $row->getCurrencies();

        $html = '';
        if ($currencies) {
            $currencySymbol = '';
            $availableCurrencyCodes = $this->storeManager->getStore()->getAvailableCurrencyCodes();
            foreach ($currencies->getasarrayCurrency() as $currency) {
                $currencyCode = $this->commMessagingHelper->getCurrencyMapping($currency->getCurrencyCode(), 'e2m');
                $availableCurrencyCode = in_array($currencyCode, $availableCurrencyCodes) ? $currencyCode : null;
                //M1 > M2 Translation Begin (Rule p2-6.4)
                //$currencySymbol = $availableCurrencyCode ? Mage::app()->getLocale()->currency($availableCurrencyCode)->getSymbol() : null;
                $currencySymbol = $availableCurrencyCode ? $this->currency->create()->load($availableCurrencyCode)->getCurrencySymbol() : null;
                //M1 > M2 Translation End
                $html .= $currencySymbol . " " . $currency->getContractPrice() . "</br>";
            }
        }

        return $html;
    }

}
