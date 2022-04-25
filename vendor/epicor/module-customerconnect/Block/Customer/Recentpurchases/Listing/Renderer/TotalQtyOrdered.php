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
class TotalQtyOrdered extends \Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    private $customerconnectHelper;

    /**
     * TotalQtyOrdered constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Epicor\Customerconnect\Helper\Data $customerconnectHelper
     * @param array $data
     */
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

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $id = $row->getId();

        if (!empty($id)) {
            $html = $row->getTotalQtyOrdered();
            //NB you don't need to replace the entire row for this type of grid, just add the content
            if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_recentpurchases_reorder")
                && $this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_recentpurchases_edit")) {
                    $html = '<input value="' . $row->getTotalQtyOrdered() . '" name="col-total_qty_ordered_name_' . $row->getProductCode() . '" class="col-total_qty_ordered_' . $row->getProductCode() . '" />';
                }
        }

        return $html;
    }
}
