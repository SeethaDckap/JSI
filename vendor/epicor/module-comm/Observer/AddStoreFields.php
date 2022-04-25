<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AddStoreFields extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Add Branding Fields to Website / Store Edit Pages
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $section = '';
        $storeData = $this->registry->registry('store_data');
        $websiteData = $this->dataObjectFactory->create(array());
        if ($this->registry->registry('store_type') == 'website') {
            $section = 'website';
        } elseif ($this->registry->registry('store_type') == 'group') {
            $section = 'group';
            $websiteData = $storeData->getWebsite() ? $storeData->getWebsite() : $websiteData;
        } elseif ($this->registry->registry('store_type') == 'store') {
            return $this;
        }

        $form = $block->getForm();
        $form->setEnctype('multipart/form-data');
        /* @var $form Varien_Data_Form */
        $fieldset = $form->addFieldset('group_branding', array(
            'legend' => __('Brand Information')
        ));

        $fieldset->addField('ecc_company', 'text', array(
            'name' => $section . '[ecc_company]',
            'label' => __('Company'),
            'value' => $websiteData->getEccCompany() ?: $storeData->getEccCompany(),
            'disabled' => $websiteData->getEccCompany() ? true : false,
        ));

        $fieldset->addField('ecc_site', 'text', array(
            'name' => $section . '[ecc_site]',
            'label' => __('Site'),
            'value' => $websiteData->getEccSite() ?: $storeData->getEccSite(),
            'disabled' => $websiteData->getEccSite() ? true : false,
        ));

        $fieldset->addField('ecc_warehouse', 'text', array(
            'name' => $section . '[ecc_warehouse]',
            'label' => __('Warehouse'),
            'value' => $websiteData->getEccWarehouse() ?: $storeData->getEccWarehouse(),
            'disabled' => $websiteData->getEccWarehouse() ? true : false,
        ));

        $fieldset->addField('ecc_group', 'text', array(
            'name' => $section . '[ecc_group]',
            'label' => __('Group'),
            'value' => $websiteData->getEccGroup() ?: $storeData->getEccGroup(),
            'disabled' => $websiteData->getEccGroup() ? true : false,
        ));

        if ($section == 'group') {
            $fieldset->addField('ecc_storeswitcher', 'select', array(
                'label' => __('Shown on Brand Selector'),
                'name' => $section . '[ecc_storeswitcher]',
                'options' => $this->configConfigSourceYesno->toArray(),
                'value' => $storeData->getEccStoreswitcher(),
                'after_element_html' => '</br> Is store to be displayed on the brand selector landing page?',
            ));

            $url = '';
            if ($storeData->getEccBrandimage()) {
                //M1 > M2 Translation Begin (Rule p2-5.3)
                //$url = Mage::getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_MEDIA) . 'brandimage/' . $storeData->getEccBrandimage();
                $url = $this->_getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'brandimage/' . $storeData->getEccBrandimage();
                //M1 > M2 Translation End
            }

            $fieldset->addField('ecc_brand_image', 'image', array(
                'label' => __('Brand Image'),
                'name' => 'ecc_brandimage',
                'value' => $url,
                'after_element_html' => '</br>Allowed file types: PNG, GIF, JPG, JPEG, SVG',
            ));
        }

        $fieldset = $form->addFieldset('ecc_allowed_customer_types_group', array(
            'legend' => __('Customer Types')
        ));

        $webAllowedTypes = explode(',', $websiteData->getEccAllowedCustomerTypes());
        $allowedTypes = explode(',', $storeData->getEccAllowedCustomerTypes());

        $values = $this->commConfigSourceCustomertypes->toOptionArray();

        $processedValues = array();
        if ($webAllowedTypes) {
            foreach ($values as $value) {
                if (in_array($value['value'], $webAllowedTypes)) {
                    $value['style'] = 'text-decoration:underline';
                }
                $processedValues[] = $value;
            }
        }

        $storeLabel = __('Underlined Types are the Types that are Allowed at Website Level');

        $fieldset->addField('ecc_allowed_customer_types', 'multiselect', array(
            'label' => __('Allowed Customer Types'),
            'name' => 'ecc_allowed_customer_types',
            'values' => $processedValues,
            'value' => $allowedTypes,
            'after_element_html' => $section == 'group' ? '</br>' . $storeLabel : '',
        ));

        if ($section == 'group' || $section == 'website') {
            if ($this->scopeConfig->getValue('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $existingLists = array();
                $websiteLists = array();
                $websiteListCollection = $this->listsResourceListModelWebsiteCollectionFactory->create()// get values from STORE
                    ->addFieldToFilter('website_id', array('eq' => $storeData->getWebsiteId()));
                if ($section == 'group') {
                    $listCollection = $this->listsResourceListModelStoreGroupCollectionFactory->create()// get values from STORE
                        ->addFieldToFilter('store_group_id', array('eq' => $storeData->getGroupId()));
                    foreach ($websiteListCollection as $webListColl) {
                        $websiteLists[$webListColl->getListId()] = $webListColl->getListId();
                    }
                } else {
                    $listCollection = $websiteListCollection;
                }
                foreach ($listCollection as $listColl) {
                    $existingLists[$listColl->getListId()] = $listColl->getListId();
                }

                $fieldset = $form->addFieldset("{$section}_lists", array(
                    'legend' => __('List Information')
                ));
                $label = $section == 'website' ? 'Website' : 'Store';
                $allowedValues = $this->listsConfigSourceLists->toOptionArray();
                $allowedValuesWithStyle = array();
                foreach ($allowedValues as $allowedValue) {                           // compare store lists with website lists, if on website list, underline
                    if (in_array($allowedValue['value'], $websiteLists)) {
                        $allowedValue['style'] = 'text-decoration:underline';
                    }
                    $allowedValuesWithStyle[] = $allowedValue;
                }
                $fieldset->addField('lists', 'multiselect', array(
                    'label' => __("Lists Linked to this {$label}"),
                    'name' => $section . '[lists]',
                    'values' => $allowedValuesWithStyle,
                    'value' => $existingLists,
                    'after_element_html' => $section == 'group' ? "</br> Underlined Lists are Lists linked at Website Level" : null,
                ));
            }
        }

        $block->setForm($form);
    }

}