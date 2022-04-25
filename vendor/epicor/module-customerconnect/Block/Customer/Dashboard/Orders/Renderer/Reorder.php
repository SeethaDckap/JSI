<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer;

use \Epicor\Customerconnect\Model\DocumentPrint;
use phpDocumentor\Reflection\Types\Static_;

/**
 * Order Reorder link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Reorder extends \Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input
{
    const FRONTEND_RESOURCE_ORDER_PRINT = "Epicor_Customerconnect::customerconnect_account_orders_print";
    const FRONTEND_RESOURCE_ORDER_EMAIL = "Epicor_Customerconnect::customerconnect_account_orders_email";
    const FRONTEND_RESOURCE_ORDER_REORDER = "Epicor_Customerconnect::customerconnect_account_orders_reorder";

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
            $html = $this->getReorderLinkHtml($row);
            $active = $this->_scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/active');

            $html .= $this->getPrintAction($row);
        }

        return $html;
    }

    private function getPrintAction($row)
    {
        return $this->getLayout()
            ->createBlock('\Epicor\Customerconnect\Block\DocumentPrint\ActionLinks')
            ->setData('account_number', $this->customerconnectHelper->getErpAccountNumber())
            ->setData('entity_key', $row->getData('order_number'))
            ->setData('entity_document', 'Order')
            ->setData('access_print', static::FRONTEND_RESOURCE_ORDER_PRINT)
            ->setData('access_email', static::FRONTEND_RESOURCE_ORDER_EMAIL)
            ->setData('order_level_attachment', $row->getData('attachments'))
            ->setTemplate('Epicor_Customerconnect::customerconnect/document_print/actionlink.phtml')
            ->toHtml();
    }

    private function getReorderLinkHtml($row)
    {
        if (!$this->_isAccessAllowed(static::FRONTEND_RESOURCE_ORDER_REORDER) ||
            !$this->_isAccessAllowed('Epicor_Checkout::checkout_checkout_can_checkout')) {
            return '';
        }
        $helper = $this->customerconnectHelper;
        $return = $this->getUrl('customerconnect/orders/');

        return '<a 
            title="Reorder"
            href="' . $helper->getOrderReorderUrl($row, $return) . '" 
            class="link-reorder reorder-button">
            <img class="action-img-link" src="'.$this->getViewFileUrl("Epicor_Customerconnect::epicor/customerconnect/images/icons8-shopping-cart-40.png").'"

</a>';
    }

}
