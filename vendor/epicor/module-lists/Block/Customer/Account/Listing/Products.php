<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing;


/**
 * Products Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'customer_account_list_products';
        $this->_blockGroup = 'epicor_lists';
        $this->_headerText = __('Products');
        parent::__construct(
            $context,
            $data
        );
        $this->_removeButton('add');
    }

}
