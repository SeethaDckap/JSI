<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml;


/**
 * Role Admin actions
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Roles extends \Magento\Backend\Block\Widget\Grid\Container
{
 
    public function _construct()
    {
        $this->_controller = 'adminhtml_roles';
        $this->_blockGroup = 'Epicor_AccessRight';
        $this->_headerText = __('Access Control - Roles');
        $this->_addButtonLabel = __('Add New Role');

        parent::_construct();

    }
}
