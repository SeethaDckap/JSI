<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals;


class Listing extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'Epicor_Common::widget/grid/container.phtml';

    /**
     * Listing constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_controller = 'approvals_listing';
        $this->_blockGroup = 'Epicor_OrderApproval';
        $this->_headerText = __('My Order Approvals');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }
}