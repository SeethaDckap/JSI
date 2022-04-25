<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\ReturnModel;


/**
 * Customer Return collection model
 * 
 * @category   Epicor
 * @package    Epicor_License
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Customer\ReturnModel\Collection
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->commHelper = $commHelper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Customer\ReturnModel', 'Epicor\Comm\Model\ResourceModel\Customer\ReturnModel');
    }

    /**
     * Adds a filter by the customer
     * 
     * @param \Epicor\Comm\Model\Customer $customer
     * 
     * @return \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Collection
     */
    public function filterByCustomer($customer)
    {
        if ($customer->isCustomer()) {
            $commHelper = $this->commHelper;
            /* @var $commHelper \Epicor\Comm\Helper\Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            $this->getSelect()
                ->where(
                    'main_table.customer_id = ' . $customer->getId()
                    . ' OR (main_table.erp_account_id = ' . $erpAccount->getId() . ')'
            );
        } else {
            $this->addFieldToFilter('main_table.customer_id', $customer->getId());
        }

        return $this;
    }

    /**
     * Adds a filter by a guest
     * 
     * @param string $name
     * @param string $email
     * 
     * @return \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Collection
     */
    public function filterByGuest($name, $email)
    {
        //$this->addFieldToFilter('main_table.customer_name', $name);
        $this->addFieldToFilter('main_table.email_address', $email);

        return $this;
    }

}
