<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Quote\Address;

/**
 * Interface \Epicor\Customerconnect\Model\ArPayment\Quote\Address\CustomAttributeListInterface
 *
 */
interface CustomAttributeListInterface
{
    /**
     * Retrieve list of quote addresss custom attributes
     *
     * @return array
     */
    public function getAttributes();
}
