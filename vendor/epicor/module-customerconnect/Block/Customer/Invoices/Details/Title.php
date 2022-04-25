<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details;


/**
 * Invoice Details page title
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Title extends \Epicor\Customerconnect\Block\Customer\Title
{

    const FRONTEND_RESOURCE_REORDER = "Epicor_Customerconnect::customerconnect_account_invoices_reorder";

    const FRONTEND_RESOURCE_RETURN = "Epicor_Customerconnect::customerconnect_account_invoices_return";

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        parent::__construct(
            $context,
            $commonAccessHelper,
            $commReturnsHelper,
            $customerconnectHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_setReorderUrl();
        $this->_setReturnUrl();
    }

    /**
     * Sets the Reorder link url
     */
    protected function _setReorderUrl()
    {
        $invoice = $this->registry->registry('customer_connect_invoices_details');
        if ($invoice) {
            $helper = $this->customerconnectHelper;

            $this->_reorderUrl = $helper->getInvoiceReorderUrl($invoice, $this->_urlBuilder->getCurrentUrl());
        }
    }

    /**
     * Sets the Return link url
     */
    protected function _setReturnUrl()
    {
        $invoice = $this->registry->registry('customer_connect_invoices_details');
        if ($invoice) {
            $helper = $this->commReturnsHelper;

            $this->_returnUrl = $helper->getInvoiceReturnUrl($invoice);
        }
    }

}
