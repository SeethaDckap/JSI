<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage;


/**
 * Sales Rep Account Hierarchy Children List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Children extends \Epicor\Common\Block\Generic\Listing
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        array $data = [])
    {
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;

        parent::__construct($context, $data);
    }


    protected function _setupGrid()
    {
        $this->_controller = 'account_manage_children';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('Child Accounts');
    }

    protected function _prepareLayout()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        if ($helper->canAddChildrenAccounts()) {
            $this->addButton('add_button', array(
                'label' => __('Add'),
                'onclick' => "javascript:\$('child_account_add_form').show()",
                'class' => 'task'
            ));
        }

        return parent::_prepareLayout();
    }
    
    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    } 
}
