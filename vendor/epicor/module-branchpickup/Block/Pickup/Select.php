<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Pickup;


/**
 * Branchpickup page select page grid
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
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
        $this->_controller = 'pickup_select';
        $this->_headerText = __('Select Branch');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }

}
