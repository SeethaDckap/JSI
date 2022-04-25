<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Change Company Link section
 */
class Companylink implements SectionSourceInterface
{
    const FRONTEND_RESOURCE = "Epicor_Customerconnect::change_company_link";

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_authorization;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\AccessRight\Model\Authorization $authorization
    ) {
        $this->customerSession = $customerSession;
        $this->_authorization = $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $customer = $this->customerSession->getCustomer();
        $erpAccounts = $customer->getErpAcctCounts();
        if(!$this->_isAccessAllowed(static::FRONTEND_RESOURCE)
            || empty($erpAccounts) || count($erpAccounts) == 1){
            return [
                'showlink' => null
            ];
        }

        return [
            'showlink' => 1
        ];
    }

    /**
     * @param $code
     * @return bool
     */
    public function _isAccessAllowed($code)
    {
        return $this->_authorization->isAllowed($code);
    }
}

