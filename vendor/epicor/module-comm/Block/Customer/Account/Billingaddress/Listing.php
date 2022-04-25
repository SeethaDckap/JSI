<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Account\Billingaddress;


/**
 * Customer Orders list
 */
class Listing extends \Magento\Backend\Block\Widget\Grid\Container
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
        $this->_blockGroup = 'Epicor_Comm';
        $this->_controller = 'customer_account_billingaddress_listing';
        $this->_headerText = __('Billing');
        parent::__construct(
            $context,
            $data
        );
        $this->removeButton('add');
    }    

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}