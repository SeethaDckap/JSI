<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Class QuotePlugin
 * @package Epicor\Comm\Plugin
 */
class QuotePlugin
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * QuotePlugin constructor.
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        QuoteResource $quoteResource
    ) {
        $this->quoteResource = $quoteResource;
    }

    /**
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @throws AlreadyExistsException
     */
    public function afterRemoveItem(Quote $subject,Quote $result)
    {
        return $this->setEccQuote($result);
    }

    /**
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     * @throws AlreadyExistsException
     */
    public function afterRemoveAllItems(Quote $subject, Quote $result)
    {
        return $this->setEccQuote($result);
    }

    /**
     * Setting ecc_quote_id and ecc_erp_quote_id to null
     * after removing all the items in the cart or truncate the cart
     *
     * @param Quote $result
     * @return Quote
     * @throws AlreadyExistsException
     */
    private function setEccQuote(Quote $result)
    {
        if($result->hasEccQuoteId() && !$result->hasItems()) {
            $result->setEccQuoteId(null);
            $result->setEccErpQuoteId(null);
            $this->quoteResource->save($result);
        }

        return $result;
    }
}
