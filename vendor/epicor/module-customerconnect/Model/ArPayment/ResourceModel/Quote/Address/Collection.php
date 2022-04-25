<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote\Address;

/**
 * Quote addresses collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ar_sales_quote_address_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'quote_address_collection';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Epicor\Customerconnect\Model\ArPayment\Quote\Address::class,
            \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote\Address::class
        );
    }

    /**
     * Setting filter on quote_id field but if quote_id is 0
     * we should exclude loading junk data from DB
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteFilter($quoteId)
    {
        $this->addFieldToFilter('quote_id', $quoteId ? $quoteId : ['null' => 1]);
        return $this;
    }

    /**
     * Redeclare after load method for dispatch event
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);

        return $this;
    }
}
