<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Checkout\Onepage\Progress\Salesrep;


class Contact extends \Magento\Checkout\Block\Onepage
{

    private $_salesrepCustomer;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
    }
    public function getCurrentContact()
    {
        if (!$this->_salesrepCustomer) {
            $info = $this->getQuote()->getEccSalesrepChosenCustomerInfo();
            $useInfo = (!empty($info)) ? unserialize($info) : array('name' => 'N/A');
            $this->_salesrepCustomer = $this->dataObjectFactory->create($useInfo);
        }

        return $this->_salesrepCustomer;
    }

    public function getSalesrepCustomerName()
    {
        return $this->getCurrentContact()->getName();
    }

    public function getSalesrepCustomerEmail()
    {
        return $this->getCurrentContact()->getEmail();
    }

}
