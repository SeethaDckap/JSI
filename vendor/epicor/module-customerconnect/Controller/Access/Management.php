<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Access;


/**
 * 
 * Customer Access Groups management controller
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class Management extends \Epicor\Common\Controller\Access\Management\Generic
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->commHelper = $commHelper;
        $this->registry = $registry;
    }
    /**
     * Loads the erp account for this customer
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    protected function loadErpAccount()
    {

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        $erpAccount = $helper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $this->registry->register('access_erp_account', $erpAccount);

        return $erpAccount;
    }

}
