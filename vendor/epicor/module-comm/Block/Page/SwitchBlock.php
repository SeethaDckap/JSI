<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Page;

/**
 * Store and language switcher block
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SwitchBlock extends \Magento\Store\Block\Switcher
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
    }
    public function getRawStores()
    {
        if (!$this->hasData('raw_stores')) {

            parent::getRawStores();

            //$storeIds = Mage::helper('epicor_comm')->getCustomerStores($customer);
            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

            $stores = $this->getData('raw_stores');
            $tempStores = array();
            foreach ($stores as $groupId => $groupStores) {
                foreach ($groupStores as $store) {
                    if (!$erpAccount || $erpAccount->isValidForStore($store)) {
                        $tempStores[$groupId][$store->getId()] = $store;
                    }
                }
            }

            $this->setData('raw_stores', $tempStores);
        }

        return $this->getData('raw_stores');
    }

    public function getGroups()
    {
        if ($this->getIsStoreSelector()) {
            if (!$this->hasData('groups')) {
                $rawGroups = $this->getRawGroups();
                $rawStores = $this->getRawStores();

                $groups = array();
                $localeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                foreach ($rawGroups as $group) {
                    /* @var $group Mage_Core_Model_Store_Group */
                    if (!isset($rawStores[$group->getId()])) {
                        continue;
                    }
                    if ($group->getId() == $this->getCurrentGroupId()) {
                        $groups[] = $group;
                        continue;
                    }

                    $store = $group->getDefaultStoreByLocale($localeCode);
                    $store->setHomeUrl($this->getUrl('epicor_comm/store/select', array('code' => $store->getCode())));

                    if ($store) {
                        $group->setHomeUrl($store->getHomeUrl());
                        $groups[] = $group;
                    }
                }
                $this->setData('groups', $groups);
            }
        }
        return parent::getGroups();
    }

}
