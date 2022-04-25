<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickupsearch;

class Select extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'Epicor_Common::widget/grid/container.phtml';

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_blockGroup = 'Epicor_BranchPickup';
        $this->_controller = 'pickupsearch_select';
        $this->_headerText = __('Select Branch');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }

}