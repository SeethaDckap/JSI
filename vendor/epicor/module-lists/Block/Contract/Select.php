<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Contract;


/**
 * Contract select page grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Select extends \Magento\Backend\Block\Widget\Grid\Container
{

    protected $_template = 'Epicor_Common::widget/grid/container.phtml';
    
    
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'contract_select';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Select Contract');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }
    
//    protected function _setupGrid()
//    {
//        $this->_controller = 'contract_select';
//        $this->_blockGroup = 'Epicor_Lists';
//        $this->_headerText = __('Select Contract');
//        $this->removeButton('add');
//
//    }
//
//    protected function _postSetup()
//    {
//        $this->setBoxed(true);
//        parent::_postSetup();
//    }

}
