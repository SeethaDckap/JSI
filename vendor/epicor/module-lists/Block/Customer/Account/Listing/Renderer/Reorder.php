<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer;


/**
 * Invoice Reorder link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Reorder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
    }
    public function render(\Magento\Framework\DataObject $row)
    {

        $html = '';

        $id = $row->getId();

        if (!empty($id)) {

            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Data */

            $html = '<a href="' . $helper->getInvoiceReorderUrl($row) . '"class="link-reorder reorder-button">' . __('Reorder') . '</a>';
        }

        return $html;
    }

}
