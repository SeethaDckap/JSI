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
class CentrallyCollected extends \Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input
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
        $index = $this->getColumn()->getIndex();
        $html = "<span>".(($row->getData($index) && $row->getData($index) === 'Y')?'&#10004;': '&#10006;')."</span>";
        return $html;
    }
}
