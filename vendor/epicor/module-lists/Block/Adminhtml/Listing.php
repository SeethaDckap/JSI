<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml;


/**
 * List Admin actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Listing extends \Magento\Backend\Block\Widget\Grid\Container
{
 
    public function _construct()
    {
        $this->_controller = 'adminhtml_listing';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('List');
        $this->_addButtonLabel = __('Add New List');

        parent::_construct();

        $this->addButton('addbycsv', array(
            'label' => __('Add List By CSV'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/addbycsv') . '\')',
            'class' => 'add',
        ));

    }
}
