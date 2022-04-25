<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Helper;


class Data extends \Epicor\Comm\Helper\Messaging
{

    private function _createGUID()
    {
        $sectionlen = 5;
        $len = 20;
        $a = strtoupper(str_replace('.', '', uniqid('', true)));
        $hash = substr($a, 0, $sectionlen);
        for ($i = $sectionlen; $i < $len; $i += $sectionlen) {
            $hash .= '-' . substr($a, $i, 5);
        }
        return $hash;
    }

    /**
     * Sets a new preregistered password for a customer if no password exists
     * @param \Epicor\Comm\Model\Erp\Customer\Group $group
     */
    public function setPreregPassword(&$group)
    {
        if ($group->getPreRegPassword() == '') {
            $group->setPreRegPassword($this->_createGUID());
        }
    }

    public function canModifiyOrderLine($orderLineStatusCode, $type)
    {
        $allowedTypes = $this->scopeConfig->getValue('epicor_b2b_enabled_messages/som_request/allowed_actions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $validTypes = explode(',', $allowedTypes);
        $configPath = 'epicor_b2b_enabled_messages/som_request/allowed_line_statuses';
        return in_array($type, $validTypes) && $this->compareStatuses($orderLineStatusCode, $configPath);
    }

    public function canModifiyOrder($orderStatusCode)
    {
        $configPath = 'epicor_b2b_enabled_messages/som_request/allowed_order_statuses';
        return $this->compareStatuses($orderStatusCode, $configPath);
    }

    public function compareStatuses($status, $configPath)
    {
        $allowedStatuses = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $validOrderStatuses = explode(',', $allowedStatuses);
        return in_array($status, $validOrderStatuses) && $this->scopeConfig->getValue('epicor_b2b_enabled_messages/som_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
