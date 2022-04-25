<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Listing\Renderer;


/**
 * Order status "Open" display, shows Yes / No based on status value
 *
 * @author Pradeep.Kumar
 */
class Orderstatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {

        $index = $this->getColumn()->getIndex();
        $status = $row->getData($index);

        return ($status == 'O') ? 'Open' : 'Closed';
    }

}
