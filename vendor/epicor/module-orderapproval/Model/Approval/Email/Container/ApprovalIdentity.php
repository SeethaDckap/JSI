<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval\Email\Container;

use Magento\Sales\Model\Order\Email\Container\Container as EmailContainer;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;

class ApprovalIdentity extends EmailContainer implements IdentityInterface
{
    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_COPY_METHOD = 'ecc_order_approval/requestor_pending/copy_method';
    const XML_PATH_EMAIL_COPY_TO = 'ecc_order_approval/requestor_pending/copy_to';
    const XML_PATH_EMAIL_IDENTITY = 'ecc_order_approval/requestor_pending/identity';
    const XML_PATH_EMAIL_GUEST_TEMPLATE = 'ecc_order_approval/requestor_pending/guest_template';
    const XML_PATH_EMAIL_TEMPLATE = 'ecc_order_approval/requestor_pending/template';
    const XML_PATH_EMAIL_ENABLED = 'ecc_order_approval/requestor_pending/enabled';

    /**
     * Is email enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO,
            $this->getStore()->getStoreId());
        if ( ! empty($data)) {
            return array_map('trim', explode(',', $data));
        }

        return false;
    }

    /**
     * Return copy method
     *
     * @return mixed
     */
    public function getCopyMethod()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD,
            $this->getStore()->getStoreId());
    }

    /**
     * Return guest template id
     *
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE,
            $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE,
            $this->getStore()->getStoreId());
    }

    /**
     * Return email identity
     *
     * @return mixed
     */
    public function getEmailIdentity()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY,
            $this->getStore()->getStoreId());
    }
}
