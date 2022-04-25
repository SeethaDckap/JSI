<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Listing\Renderer;


/**
 * Order status "confirmed" display, shows confirmed / rejected based on status value
 *
 * @author Pradeep.Kumar
 */
class Confirmed extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

        switch ($status) {
            case 'C':
                return 'Confirmed';
                break;
            case 'R':
                return 'Rejected';
                break;
            case 'NC':
                return 'Not Confirmed';
                break;
        }
    }

}
