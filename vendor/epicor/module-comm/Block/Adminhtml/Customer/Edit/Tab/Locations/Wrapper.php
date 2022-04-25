<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Edit\Tab\Locations;


/**
 * Locations override of wrapper tab
 *
 * @author Paul.Ketelle
 */
class Wrapper extends \Epicor\Common\Block\Adminhtml\Widget\Tab\Wrapper
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }
    /**
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = $this->registry->registry('current_customer');
        }
        return $this->_customer;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->getCustomer()->isCustomer(false);
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !$this->getCustomer()->isCustomer(false);
    }

}
