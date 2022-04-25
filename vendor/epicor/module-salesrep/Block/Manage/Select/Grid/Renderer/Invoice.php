<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Manage\Select\Grid\Renderer;


/**
 * Column Renderer for Sales ERP Account Select Grid Address
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Invoice extends \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\Address
{

    public function render(\Magento\Framework\DataObject $row)
    {
        return parent::renderAddress($row, 'invoice');
    }

}
