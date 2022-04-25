<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details;


class Totals extends \Epicor\Common\Block\Generic\Totals
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_invoices_misc';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $contract;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $contract,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commHelper = $commHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        $this->contract = $contract;
        parent::__construct($context, $commonHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $invoice = $this->registry->registry('customer_connect_invoices_details');
        $columns = 10;
        $locHelper = $this->commLocationsHelper;
        $showLoc = ($locHelper->isLocationsEnabled()) ? $locHelper->showIn('cc_invoices') : false;

        if (!$showLoc) {
            $columns = 9;
        }
        if ($this->contract->contractsDisabled()) {
            $columns = $columns - 1;
        }

        if ($invoice) {

            $helper = $this->commMessagingHelper;

            $currencyCode = $helper->getCurrencyMapping($invoice->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO);

            $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount($invoice->getGoodsTotal(), $currencyCode), 'subtotal');

            if($invoice->getMiscellaneousChargesTotal() && $this->canShowMisc()){
                $expandDef = $this->customerconnectHelper->checkCusMiscView();
                $this->addRow('Miscellaneous Charges:', $helper->getCurrencyConvertedAmount($invoice->getMiscellaneousChargesTotal(), $currencyCode), 'misc', '', 0, 0, true, $expandDef);
                $this->addSubRow('misc', $invoice->getMiscellaneousCharges());
                $columns++;
            }

            $grandTot = $this->canShowMisc() ? $invoice->getGoodsTotal() + $invoice->getTaxAmount() : $invoice->getGrandTotal();

            if (!$this->commHelper->removeTaxLine($invoice->getTaxAmount())) {
                $this->addRow('Tax :', $helper->getCurrencyConvertedAmount($invoice->getTaxAmount(), $currencyCode));
            }

            $this->addRow('Grand Total :', $helper->getCurrencyConvertedAmount($grandTot, $currencyCode), 'grand_total');
        }


        // add column if lists enabled
        if ($this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $columns++;
        }


        $this->setColumns($columns - 1);
    }

    public function isHidePricesActive()
    {
        return (bool)$this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1, 2, 3]);
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ);
        return $showMiscCharges && $isMiscAllowed;
    }


}
