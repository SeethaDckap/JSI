<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders;


/**
 * Supplier Connect RFQ Block Template
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Template extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_EDIT = 'Epicor_Supplier::supplier_orders_edit';


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }

}
