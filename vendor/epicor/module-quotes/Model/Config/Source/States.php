<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\Config\Source;


class States
{

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    public function __construct(
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory
    ) {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
    }
    public function toOptionArray()
    {
        $optionArray = array();
        $statuses = $this->quotesQuoteFactory->create()->getQuoteStatuses();
        foreach ($statuses as $value => $label) {
            $optionArray[] = array('value' => $value, 'label' => $label);
        }
        return $optionArray;
    }

}
