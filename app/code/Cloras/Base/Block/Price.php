<?php

namespace Cloras\Base\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

class Price extends Template
{

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->context = $context;
        parent::__construct($context, $data);
    }

    /**
     * Return the Price Url.
     *
     * @return string
     */
    public function getBasePriceUrl()
    {

        return $this->_urlBuilder->getUrl(
            'cloras/index/price',
            ['_secure' => true]
        );
    }

    /**
     * Return the Inventory Url.
     *
     * @return string
     */
    public function getBaseInventoryUrl()
    {
        return $this->_urlBuilder->getUrl(
            'cloras/index/inventory',
            ['_secure' => true]
        );
    }
}
