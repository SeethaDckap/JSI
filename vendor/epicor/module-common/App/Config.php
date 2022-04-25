<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\App;


use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \Magento\Framework\App\Config
{
    /**
     * @param string $path
     * @param string $scope
     * @param null $scopeCode
     * @return bool
     * @fixme magento2 will return true when set 'false' in system.xml
     */
    public function isSetFlag($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $flag = strtolower($this->getValue($path, $scope, $scopeCode));
        if (!empty($flag) && 'false' !== $flag) {
            return true;
        } else {
            return false;
        }
    }
}