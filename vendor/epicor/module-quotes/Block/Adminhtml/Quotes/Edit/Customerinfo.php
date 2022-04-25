<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes\Edit;


class Customerinfo extends \Epicor\Quotes\Block\Adminhtml\Quotes\Edit\AbstractBlock
{

    /**
     * 
     * @return \Magento\Framework\DataObject
     */
    public function getCustomerGroup()
    {
        return $this->getQuote()->getCustomerGroup(true);
    }

}
