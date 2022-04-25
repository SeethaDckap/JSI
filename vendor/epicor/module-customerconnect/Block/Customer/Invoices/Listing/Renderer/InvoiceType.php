<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer;

use Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input;
use Magento\Framework\DataObject;

/**
 * Invoice Type renderer
 **/
class InvoiceType extends Input
{

    const MAPPED_VALUE_TYPES = [
        'I' => 'Invoice',
        'C' => 'Credit Note',
    ];


    /**
     * Render custom column
     *
     * @param DataObject $row Row Object.
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $index = $this->getColumn()->getIndex();
        if (isset(self::MAPPED_VALUE_TYPES[$row->getData($index)])) {
            return self::MAPPED_VALUE_TYPES[$row->getData($index)];
        } else {
            return '';
        }

    }//end render()


}//end class
