<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


class Managedashboard extends \Epicor\Common\Model\AbstractModel
{

    const STATUS = 1;
    const DATE_RANGE = '30d';
    const GRID_COUNT = 5;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Common\Model\ResourceModel\Managedashboard $resource
     * @param \Epicor\Common\Model\ResourceModel\Managedashboard\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Model\ResourceModel\Managedashboard $resource,
        \Epicor\Common\Model\ResourceModel\Managedashboard\Collection $resourceCollection,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\Common\Model\ResourceModel\Managedashboard::class);
    }

    /**
     * @return $this
     */
    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    /**
     * @return $this
     */
    public function saveRel($datas)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer_id = $this->customerSession->getCustomer()->getId();
            $account_id = $this->customerSession->getCustomer()->getEccErpaccountId();
            $this->getResource()->saveRel($customer_id,$account_id, $datas);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function getDashboardConfiguration($accounttype = false)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer_id = $this->customerSession->getCustomer()->getId();
            $account_id = $this->customerSession->getCustomer()->getEccErpaccountId();
            $result = [];
            $datas = $this->getResource()->getDashboardConfiguration($customer_id, $accounttype, $account_id);
            foreach ($datas as $data) {
                $result[$data['code']] = $data;
            }
            return $result;
        }
        return [];
    }
}
