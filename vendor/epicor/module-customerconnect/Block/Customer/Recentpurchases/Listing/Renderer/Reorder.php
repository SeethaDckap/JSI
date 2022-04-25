<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Recentpurchases\Listing\Renderer;

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

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    private $customerconnectHelper;

    /**
     * Reorder constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Epicor\Customerconnect\Helper\Data $customerconnectHelper
     * @param array $data
     */
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

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $id = $row->getId();

        if (!empty($id) && $this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_recentpurchases_reorder") &&
                $this->_isAccessAllowed('Epicor_Checkout::checkout_checkout_can_checkout')) {
                $html .= $this->getReorderLinkHtml($row);
            }

        return $html;
    }

    /**
     * @param $row
     * @return string
     */
    private function getReorderLinkHtml($row)
    {
        $helper = $this->customerconnectHelper;
        return '<a title="Reorder" href="' . $helper->getRecentpurchasesReorderUrl($row) . '"class="link-reorder reorder-button">
        <img class="action-img-link" src="'.$this->getViewFileUrl("Epicor_Customerconnect::epicor/customerconnect/images/icons8-shopping-cart-40.png").'"
</a>';
    }
}
