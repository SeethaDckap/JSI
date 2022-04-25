<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Datefrom
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    public function toOptionArray()
    {
        return $this->scopeConfig->getValue('epicor_comm_auto_sync_date', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
