<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account;


/**
 * Setting button for adding new List 
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */

//class Listing extends \Epicor\Common\Block\Widget\Grid\Container
class Listing extends \Magento\Backend\Block\Widget\Grid\Container
//class Listing extends \Epicor\Common\Block\Generic\Listing
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
        $this->_controller = 'customer_account_listing';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Lists');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }

    /*
    protected function _setupGrid()
    {
        $this->_controller = 'customer_account_listing';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Lists');
        $this->removeButton('add');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    } 
    */

}
