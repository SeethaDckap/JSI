<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes\Edit;


class Quoteinfo extends \Epicor\Quotes\Block\Adminhtml\Quotes\Edit\AbstractBlock
{
    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
         \Epicor\Quotes\Helper\Data $quotesHelper,
        array $data = []
    ) {
        $this->quotesHelper = $quotesHelper;
        parent::__construct(
            $context,
             $registry,   
            $data
        );
    }
    
    public function getExpires()
    {
        return $this->quotesHelper->getHumanExpires($this->getQuote());
    }

    public function getUpdatedAt()
    {
        return $this->quotesHelper->getLocalDate($this->getQuote()->getUpdatedAt());
    }

}
