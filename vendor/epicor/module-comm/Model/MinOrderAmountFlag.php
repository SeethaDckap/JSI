<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

use Epicor\Comm\Helper\Data as CommData;
use Epicor\Dealerconnect\Model\Customer\Erpaccount;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Store\Model\ScopeInterface;

class MinOrderAmountFlag
{
    private $scopeConfig;
    private $storeManager;
    private $commHelper;
    private $erpSystemsSupportingMinOrderAmount = [
        'eclipse'
    ];
    private $registry;


    public function __construct(
        Registry $registry,
        ScopeConfig $scopeConfig,
        StoreManagerInterface $storeManager,
        CommData $commHelper
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->registry = $registry;
    }

    public function setMinOrderFlagFromCusConfig($erpAccountData)
    {
        $cusDefault = $this->getCusMinOrderDefaultFlag();

        $erpAccountData->setMinOrderAmountFlag($cusDefault);
    }

    public function isMinOrderSetToUploadInCus():bool
    {
        return (boolean) $this->scopeConfig
            ->getValue('epicor_comm_field_mapping/cus_mapping/customer_account_min_order_value_update');
    }

    public function isMinOrderSupportedByErp():bool
    {
        return in_array($this->getCurrentErpSystem(), $this->erpSystemsSupportingMinOrderAmount);
    }

    private function getCusMinOrderDefaultFlag()
    {
        return $this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/erp_account_min_order_flag_default');
    }

    public function isMinOrderActive($erpAccountId = null) :bool
    {
        $erpAccountFlag = $this->getMinOrderAmountFlag($erpAccountId);
        if($this->isMinOrderFlagErp($erpAccountFlag)){
            return true;
        }
        return $this->isMinOrderFlagGlobal($erpAccountFlag)
            ? $this->isMinOrderActiveBaseOnGlobalFlag() : $this->isMinOrderActiveBaseOnErpFlag($erpAccountId);
    }

    public function getMinOrderValueAmount($erpAccountId = null)
    {
        $minOrderAmountFlag = $this->getMinOrderAmountFlag($erpAccountId);

        if($this->isMinOrderFlagErp($minOrderAmountFlag)
            || ($this->isMinOrderFlagGlobal($minOrderAmountFlag) && $this->isCusSettingErp())){
            return $this->commHelper->getMinimumOrderAmount($erpAccountId);
        }

        if($this->isMinOrderFlagGlobal($minOrderAmountFlag) && $this->isCusSettingHighLow()){
            return $this->getMinOrderValueBasedOnCusHighLowValues($erpAccountId);
        }

        return $this->getSiteLevelMinOrderAmount();
    }

    private function isMinOrderActiveBaseOnGlobalFlag(): bool
    {
        if ($this->isCusSettingErpHighLow()) {
            return true;
        } else {
            return $this->isSiteMinOrderEnabled();
        }
    }

    private function isMinOrderActiveBaseOnErpFlag($erpAccountId = null): bool
    {
        if (!$this->getMinOrderAmountFlag($erpAccountId)) {
            return $this->isSiteMinOrderEnabled();
        }
        return ($this->isSiteMinOrderEnabled()
                && MinOrderAmountFlag::isGlobalDefaultFlagSet($this->getMinOrderAmountFlag($erpAccountId)))
            || MinOrderAmountFlag::isErpAccountFlagSet($this->getMinOrderAmountFlag($erpAccountId));
    }

    private function isCusSettingErpHighLow(): bool
    {
        return in_array($this->getCusMinOrderSetting(), ['erp','higher','lower']) ? true : false;
    }

    private function isCusSettingErp(): bool
    {
        $setting = $this->getCusMinOrderSetting();
        return in_array($setting, ['erp']) ? true : false;
    }

    private function isCusSettingHighLow(): bool
    {
        return in_array($this->getCusMinOrderSetting(), ['higher','lower']) ? true : false;
    }

    private function getCusMinOrderSetting(): string
    {
        return $this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/cus_min_order');
    }

    private function isMinOrderFlagGlobal($flag)
    {
        return !$flag ? true : false;
    }

    private function isMinOrderFlagErp($flag)
    {
        return $flag ? true : false;
    }

    private function isErpAccountFlagGlobal($erpAccountId)
    {
        $flag = $this->getMinOrderAmountFlag($erpAccountId);
        return $this->isMinOrderFlagGlobal($flag);
    }

    private function getMinOrderValueBasedOnCusHighLowValues($erpAccountId)
    {
        if($this->isErpAccountFlagGlobal($erpAccountId) && !$this->isSiteMinOrderEnabled() && $this->isCusSettingHighLow()){
            return $this->getErpAccountMinOrderAmount($erpAccountId);
        }
        if ($erpAccountId && $this->getCusMinOrderSetting() === 'lower') {
            return $this->getLowerMinOrderValue($erpAccountId);
        }

        if ($erpAccountId && $this->getCusMinOrderSetting() === 'higher') {
            return $this->getHigherMinOrderValue($erpAccountId);
        }

        return $this->getSiteLevelMinOrderAmount();
    }

    private function getErpAccountMinOrderAmount($erpAccountId)
    {
        $erpAccount = $this->getCustomerErpAccount($erpAccountId);
        if($erpAccount){
            return $erpAccount->getMinOrderAmount();
        }
    }

    private function getLowerMinOrderValue($erpAccountId)
    {
        if ($erpAccountId) {
            return min($this->getSiteLevelMinOrderAmount(), $this->getErpAccountMinOrderAmount($erpAccountId));
        }
    }

    private function getHigherMinOrderValue($erpAccountId)
    {
        $erpAccount = $this->getCustomerErpAccount($erpAccountId);
        if ($erpAccountId) {
            return max($this->getSiteLevelMinOrderAmount(), $erpAccount->getMinOrderAmount());
        }
    }

    public function isErpCurrencyGridEditable(): bool
    {
        if ($this->isMinOrderSupportedByErp()) {
            return false;
        }

        if ($this->isGlobalDefaultFlagSet($this->getMinOrderAmountFlag())) {
            return false;
        }

        return true;
    }

    private function getCurrentErpSystem()
    {
        return $this->scopeConfig->getValue('Epicor_Comm/licensing/erp');
    }

    private function isSiteMinOrderEnabled()
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    private function getMinOrderAmountFlag($erpAccountId = null): int
    {
        $erpAccount = $this->getCustomerErpAccount($erpAccountId);
        if ($erpAccount instanceof Erpaccount) {
            return (int) $erpAccount->getMinOrderAmountFlag();
        }
        return (int) 0;
    }

    private function getSiteLevelMinOrderAmount()
    {
        return $this->scopeConfig->getValue(
            'sales/minimum_order/amount',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    private function getCustomerErpAccount($accountId = null)
    {
        $erpAccount = $this->registry->registry('customer_erp_account');
        if ($erpAccount instanceof Erpaccount) {
            return $erpAccount;
        }
        return $this->getEccErpAccount($accountId);
    }

    private function getEccErpAccount($accountId)
    {
        $erpAccount = $this->registry->registry('ecc_erp_account');
        if(!$erpAccount && $accountId){
            $erpAccount = $this->registry->registry('ecc_erp_account_' . $accountId);
        }
        if ($erpAccount instanceof Erpaccount) {
            return $erpAccount;
        }
    }

    public static function isGlobalDefaultFlagSet(int $valueSet)
    {
        return $valueSet === 0 ? true:false;
    }

    public static function isErpAccountFlagSet(int $valueSet)
    {
        return $valueSet === 1 ? true:false;
    }

    public static function getOptions(): array
    {
        return [
            0 => 'Global Default',
            1 => 'ERP Account'
        ];
    }

    public static function getMinOrderAmountFlagOptionsHtml(int $valueSet)
    {
        $optionsHtml = '';

        foreach (self::getOptions() as $value => $description) {
            $optionsHtml .= '<option value="' . $value . '"';
            if ($value === $valueSet) {
                $optionsHtml .= 'selected="selected">';
            } else {
                $optionsHtml .= '>';
            }
            $optionsHtml .= $description . '</options>';
        }

        return $optionsHtml;
    }
}
