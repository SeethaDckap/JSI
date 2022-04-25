<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Languages
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localLists;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\ListsInterface $localeLists
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_localLists = $localeLists;
    }
    public function toOptionArray()
    {
        $stores = $this->storeManager->getStores();

        $languages = array();
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$locales = Mage::app()->getLocale()->getOptionLocales();
        $locales = $this->_localLists->getOptionLocales();
        //M1 > M2 Translation End
        foreach ($stores as $store) {
            $storeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

            // only add the language if we don't already have it
            if (isset($storeCode) && !isset($languages[$storeCode])) {

                $test = new \Zend_Locale($storeCode);

                $languages[$storeCode] = array(
                    'label' => $storeCode,
                    'value' => $storeCode,
                );
            }
        }
        foreach ($locales as $locale) {
            if (isset($languages[$locale['value']])) {
                $languages[$locale['value']]['label'] = $locale['label'];
            }
        }

        return $languages;
    }

}
