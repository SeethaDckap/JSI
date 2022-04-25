<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Access\Management\Groups\Details;


/**
 * 
 * Access group rights list
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Rights extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'access_management_groups_details_rights';
        $this->_blockGroup = 'epicor_common';
        $this->_headerText = __('Rights');
        parent::__construct(
            $context,
            $data
        );
        $this->_removeButton('add');
    }

}
