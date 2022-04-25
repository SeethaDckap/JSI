<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Access\Management\Groups;


/**
 * Customer access groups grid 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Listing extends \Magento\Backend\Block\Widget\Grid\Container
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

        $this->_controller = 'access_management_groups_listing';
        $this->_blockGroup = 'epicor_common';
        $this->_headerText = __('Groups');

        $this->_addButton('module_controller', array(
            'label' => __('Add Group'),
            'onclick' => "setLocation('{$this->getUrl('*/*/editgroup')}')",
            'class' => 'add'
        ));

        parent::__construct();
        $this->_removeButton('add');
    }

}
