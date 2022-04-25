<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details;


class Data extends \Epicor\Customerconnect\Block\Customer\Info
{

    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_invoices_details';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Customerconnect\Block\Listing\Renderer\LinkorderFactory
     */
    protected $customerconnectListingRendererLinkorderFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Block\Listing\Renderer\LinkorderFactory $customerconnectListingRendererLinkorderFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        $this->customerconnectListingRendererLinkorderFactory = $customerconnectListingRendererLinkorderFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $invoices = $this->registry->registry('customer_connect_invoices_details');
        //$helper = $this->customerconnectHelper;
        //$erp_account_number = $helper->getErpAccountNumber();

        //$order_requested = $helper->urlEncode($helper->encrypt($erp_account_number . ']:[' . $invoices->getOurOrderNumber()));
        if ($invoices) {
            $renderer = $this->customerconnectListingRendererLinkorderFactory->create();
            $columnData = $this->dataObjectFactory->create();
            $columnData->setIndex('our_order_number');
            $renderer->setColumn($columnData);
            $orderLink = $renderer->render($invoices);
            $invoiceDate = $invoices->getDate();
            $dueDate = $invoices->getDueDate();
            $this->_infoData = array(
                __('Invoice Date')->render() => $this->processDate($invoiceDate) ? $this->processDate($invoiceDate) : __('N/A'),
                __('Due By')->render() => $this->processDate($dueDate) ? $this->processDate($dueDate) : __('N/A'),
                __('Terms')->render() => $invoices->getPaymentTerms(),
                __('PO Number')->render() => $invoices->getCustomerReference(),
                __('Ship Via')->render() => $invoices->getDeliveryMethod(),
                __('Sales Person')->render() => $invoices->getSalesRep() ? $invoices->getSalesRep()->getName() : null,
                __('Order Number')->render() => $orderLink,
                __('FOB')->render() => $invoices->getFob(),
                __('Reseller Id')->render() => $invoices->getReseller() ? $invoices->getReseller()->getNumber() : null
            );
            if ($this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $this->_infoData[__('Contract')->render()] = $invoices->getContractCode() ? $this->commHelper->retrieveContractTitle($invoices->getContractCode()) : null;
            }
        }
        $this->setTitle(__('Invoice Information'));
    }

    /**
     *
     * Get processed date
     * @param string
     * @return string
     */
    public function processDate($rawDate)
    {
        if ($rawDate) {
            $timePart = substr($rawDate, strpos($rawDate, "T") + 1);
            //M1 > M2 Translation Begin (Rule 32)
            /*if (strpos($timePart, "00:00:00") !== false) {
                $processedDate = $this->getHelper()->getLocalDate($rawDate, \Epicor\Common\Helper\Data::DAY_FORMAT_MEDIUM, false);
            } else {
                $processedDate = $this->getHelper()->getLocalDate($rawDate, \Epicor\Common\Helper\Data::DAY_FORMAT_MEDIUM, true);
            }*/
            if (strpos($timePart, "00:00:00") !== false) {
                $processedDate = $this->getHelper()->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            } else {
                $processedDate = $this->getHelper()->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, true);
            }
            //M1 > M2 Translation End
        } else {
            $processedDate = '';
        }
        return $processedDate;
    }

}
