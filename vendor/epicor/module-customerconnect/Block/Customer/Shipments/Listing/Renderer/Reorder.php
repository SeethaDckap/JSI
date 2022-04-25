<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Shipments\Listing\Renderer;


use Epicor\Customerconnect\Model\DocumentPrint;

/**
 * Shipment Reorder link grid renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Reorder extends \Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
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
            if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_shipments_reorder") &&
                $this->_isAccessAllowed('Epicor_Checkout::checkout_checkout_can_checkout')) {
                $html .= $this->getReorderLinkHtml($row);
            }
            $html .= $this->getPrintAction($row);
        }


        return $html;
    }

    private function getPrintAction($row)
    {
        return $this->getLayout()
            ->createBlock('\Epicor\Customerconnect\Block\DocumentPrint\ActionLinks')
            ->setData('account_number', $this->customerconnectHelper->getErpAccountNumber())
            ->setData('entity_key', $row->getData('packing_slip'))
            ->setData('entity_document', 'Pack')
            ->setTemplate('Epicor_Customerconnect::customerconnect/document_print/actionlink.phtml')
            ->toHtml();
    }

    private function getReorderLinkHtml($row)
    {
        $helper = $this->customerconnectHelper;
        $return = $this->getUrl('customerconnect/orders/');

        return '<a 
            title="Reorder"
            href="' . $helper->getOrderReorderUrl($row, $return) . '" 
            class="link-reorder reorder-button"><img class="action-img-link" src="'.$this->getViewFileUrl("Epicor_Customerconnect::epicor/customerconnect/images/icons8-shopping-cart-40.png").'"
</a>';
    }
}
