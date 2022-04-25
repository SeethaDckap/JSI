<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\Skus;

use Epicor\AccessRight\Model\Authorization;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CpnuManagement
 * @package Epicor\Customerconnect\Model\Skus
 */
class CpnuManagement
{
    /**
     * Configuration path for CPNU
     */
    const XML_PATH_CPNU = 'customerconnect_enabled_messages/CPNU_request/active';

    /**
     * Configuration path for CPNU ERP update
     */
    const XML_PATH_CPNU_ERP_UPDATE = 'customerconnect_enabled_messages/CPNU_request/erp_update';

    /**
     * Access control path for delete
     */
    const FRONTEND_RESOURCE_ACCOUNT_SKU_DELETE = 'Epicor_Customerconnect::customerconnect_account_skus_delete';

    /**
     * Access control path for delete
     */
    const FRONTEND_RESOURCE_ACCOUNT_SKU_EDIT = 'Epicor_Customerconnect::customerconnect_account_skus_edit';

    /**
     * Access control path for delete
     */
    const FRONTEND_RESOURCE_ACCOUNT_SKU_ADD = 'Epicor_Customerconnect::customerconnect_account_skus_create';

    /**
     * Configuration path for CPNU Error handling Send User Notification
     */
    const XML_PATH_CPNU_SUN = 'customerconnect_enabled_messages/CPNU_request/error_user_notification';

    /**
     * Configuration path for CPNU Error handling Show ERP Error Description
     */
    const XML_PATH_CPNU_SEED = 'customerconnect_enabled_messages/CPNU_request/error_user_notification_erp';

    /**
     * Configuration path for CPNU Warning actions Send User Notification
     */
    const XML_PATH_CPNU_WUN = 'customerconnect_enabled_messages/CPNU_request/warning_user_notification';

    /**
     * Configuration path for CPNU Warning actions Show ERP Error Description
     */
    const XML_PATH_CPNU_WUNE = 'customerconnect_enabled_messages/CPNU_request/warning_user_notification_erp';

    /**
     * Configuration path for ERP
     */
    const XML_PATH_ERP = 'Epicor_Comm/licensing/erp';

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * CpnuManagement constructor.
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Authorization $authorization
     */
    public function __construct(
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        Authorization $authorization
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->authorization = $authorization;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        if ($this->isMasterShopper() && $this->isCpnuEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function isCpnuEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CPNU, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|null
     */
    private function isMasterShopper()
    {
        return $this->customerSession->getCustomer()->getData('ecc_master_shopper');
    }

    /**
     * @param $path
     * @return bool
     */
    public function isAccessAllowed($path)
    {
        return $this->authorization->isAllowed($path);
    }

    /**
     * @return bool
     */
    public function erpUpdateAllow()
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_CPNU_ERP_UPDATE, ScopeInterface::SCOPE_STORE) == 1) {
            return true;
        }

        return false;
    }
}
