<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Helper;


class Data extends \Epicor\Comm\Helper\Data
{

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag('epicor_salesrep/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Customer $salesRep
     */
    public function salesRepHasCatalogAccess($salesRep)
    {
        $salesrepAccess = $salesRep->getEccSalesrepCatalogAccess();
        $globalConfig = $this->scopeConfig->getValue('epicor_salesrep/general/catalog_allowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($globalConfig == 'forceY') {
            $salesrepAccess = 'Y';
        } else if ($globalConfig == 'forceN') {
            $salesrepAccess = 'N';
        }

        if (is_null($salesrepAccess)) {
            $salesRepAccount = $salesRep->getSalesRepAccount();
            $salesrepAccess = $salesRepAccount->getCatalogAccess();
            if (empty($salesrepAccess)) {
                $salesrepAccess = $globalConfig;
            }
        }

        return $salesrepAccess == 'Y';
    }

    /**
     * To check whether the url is secure or not for AJAX calls
     * 
     * @param ($_SERVER['HTTPS'])
     * @return boolean
     */
    public function isSecure()
    {
        $params = array();
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $params = true;
        } else {
            $params = false;
        }
        return $params;
    }

}
