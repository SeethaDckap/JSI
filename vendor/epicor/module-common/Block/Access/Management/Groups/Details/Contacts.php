<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Access\Management\Groups\Details;


/**
 * 
 * Access management group contacts list
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Contacts extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'access_management_groups_details_contacts';
        $this->_blockGroup = 'epicor_common';
        $this->_headerText = __('Contacts');
        parent::__construct(
            $context,
            $data
        );
        $this->_removeButton('add');
    }

}
