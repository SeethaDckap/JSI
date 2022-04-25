<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments;


class History extends \Epicor\Common\Block\Generic\Listing
{
     
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );
    }

    protected function _setupGrid()
    {
        $this->_controller = 'customer_arpayments_history';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('My AR Payments Received');
    }
}