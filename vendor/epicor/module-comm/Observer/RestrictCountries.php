<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class RestrictCountries implements \Magento\Framework\Event\ObserverInterface
{
    

    protected $scopeConfig;

    protected $request;

    protected $commResourceErpMappingCountryCollectionFactory;
    

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country\CollectionFactory $commResourceErpMappingCountryCollectionFactory
    ){
        
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->commResourceErpMappingCountryCollectionFactory = $commResourceErpMappingCountryCollectionFactory;
    }
    
    /**
     * Triggered when the country list is loaded
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection Mage_Directory_Model_Resource_Country_Collection */

        if ($collection instanceof \Magento\Directory\Model\ResourceModel\Country\Collection) {

            $controller = $this->request->getControllerName();

            if (!in_array($controller, array('epicorcommon_quickstart','epicorcommon_mapping_country', 'epicorcomm_mapping_country', 'system_config', 'adminhtml_mapping_country', 'adminhtml_quickstart'))) {
                if ($this->scopeConfig->isSetFlag('Epicor_Comm/address/restrict_to_mapped_countries', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                    $countryList = $this->commResourceErpMappingCountryCollectionFactory->create()->getData();

                    $mappedCountries = array();

                    foreach ($countryList as $erpCountry) {
                        $mappedCountries[] = $erpCountry['magento_id'];
                    }

                    $collection->addFieldToFilter('country_id', array('in' => $mappedCountries));
                }
            }
        }
    }

}