<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Filter;


/**
 * Model Class for List Filtering
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Erpaccounttype extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
    }
    /**
     * Adds ERP Account type filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filter($collection)
    {
        if (!($typeFilter = $this->getTypeFilter())) {
            $session = $this->customerSession;
            /* @var $session Mage_Customer_Model_Session */

            $customer = $session->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */

            $types = array('N');
            if ($customer->isGuest() && !$customer->getEccErpaccountId()) {
                $types[] = 'C'; 
		$types[] = 'E';
            } else {
                $types[] = 'E';
                $helper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */
                $erpAccountId = $customer->getId() ? $customer->getEccErpaccountId() : null;
                $erpAccount   = $helper->getErpAccountInfo($erpAccountId);
                if ($erpAccount instanceof \Epicor\Comm\Model\Customer\Erpaccount) {
                    $types[] = ($erpAccount->getAccountType() == 'B2B') ? 'B' : 'C';
                }
            }
        } else {
            $types = array($typeFilter, 'E', 'N');
        }

        $collection->addFieldToFilter('erp_account_link_type', array('in' => $types));

        return $collection;
    }

}
