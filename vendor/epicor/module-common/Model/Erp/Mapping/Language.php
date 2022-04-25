<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Erp\Mapping;


class Language extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ListsInterface $_localeLists,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_localeLists = $_localeLists;
        parent::__construct($context, $registry, $storeManager, $resource, $resourceCollection, $data);
    }


    public function _construct()
    {
        $this->_init('Epicor\Common\Model\ResourceModel\Erp\Mapping\Language');
    }

    /**
     * Get Erp Code
     * @method getErpCode()
     * @return string 
     */

    /**
     * Get Magento ISO 2 code
     * @method getLanguages()
     * @return string 
     */
    public function beforeSave()
    {
        parent::beforeSave();
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //$locales = Mage::app()->getLocale()->getOptionLocales();
        $locales = $this->_localeLists->getOptionLocales();
        //M1 > M2 Translation End
        $language_codes = array();
        foreach ($locales as $locale) {
            $language_codes[$locale['value']] = $locale['label'];
        }

        $languageCodes = $this->getLanguageCodes();

        if (!is_array($languageCodes)) {
            $languageCodes = explode(', ', $languageCodes);
        }

        $languages = array();
        $m2CodeChanges = [
            'az_AZ' => 'az_Latn_AZ',
            'bs_BA' => 'bs_Latn_BA',
            'mn_MN' => 'mn_Cyrl_MN',
            'ms_MY' => 'ms_Latn_MY',
            'sr_RS' => 'sr_Cyrl_RS',
            'zh_CN' => 'zh_Hans_CN',
            'zh_HK' => 'zh_Hant_HK',
            'zh_TW' => 'zh_Hant_TW'
        ];
        foreach ($languageCodes as $language_code) {
            if (isset($m2CodeChanges[$language_code])) {
                $language_code = $m2CodeChanges[$language_code];
            }

            if (isset($language_codes[$language_code])){
                $languages[] = $language_codes[$language_code];
            }
        }

        $this->setLanguages(implode(', ', $languages));
    }

}
