<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer;

use \Epicor\Customerconnect\Model\DocumentPrint;
/**
 * Invoice Reorder link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Reorder extends \Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input
{

    const FRONTEND_RESOURCE_INVOICES_PRINT = "Epicor_Customerconnect::customerconnect_account_invoices_print";
    const FRONTEND_RESOURCE_INVOICES_EMAIL = "Epicor_Customerconnect::customerconnect_account_invoices_email";

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $id = $row->getId();

        if (!empty($id)) {
            if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_invoices_reorder") &&
                $this->_isAccessAllowed('Epicor_Checkout::checkout_checkout_can_checkout')) {
                $html .= $this->getReorderLinkHtml($row);
            }

            $html .= $this->getLayout()
                ->createBlock('\Epicor\Customerconnect\Block\DocumentPrint\ActionLinks')
                ->setData('account_number', $this->customerconnectHelper->getErpAccountNumber())
                ->setData('entity_key', $row->getData('invoice_number'))
                ->setData('entity_document', 'Invoice')
                ->setData('access_print', static::FRONTEND_RESOURCE_INVOICES_PRINT)
                ->setData('access_email', static::FRONTEND_RESOURCE_INVOICES_EMAIL)
                ->setTemplate('Epicor_Customerconnect::customerconnect/document_print/actionlink.phtml')
                ->toHtml();

        }

        return $html;
    }

    private function getReorderLinkHtml($row)
    {
        $helper = $this->customerconnectHelper;
        return '<a title="Reorder" href="' . $helper->getInvoiceReorderUrl($row) . '"class="link-reorder reorder-button">
        <img class="action-img-link" src="'.$this->getViewFileUrl("Epicor_Customerconnect::epicor/customerconnect/images/icons8-shopping-cart-40.png").'"
</a>';
    }
}
