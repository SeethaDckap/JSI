<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Shipments\Listing\Renderer;


/**
 * Shipment Reorder link grid renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Allshippments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
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

            $helper = $this->customerconnectHelper;

            $html = '<a href="' . $helper->getShipmentReorderUrl($row) .'"class="link-reorder reorder-button">'. __('Reorder') . '</a>';
        }

        return $html;
    }

}
