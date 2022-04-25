<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Erp\Mapping;


/**
 * @method getErpCode()
 * @method getMagentoCode() 
 */
class Shipping
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->commHelper = $commHelper;
    }


    public function toOptionArray()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->getShippingmethodList();
    }

}
