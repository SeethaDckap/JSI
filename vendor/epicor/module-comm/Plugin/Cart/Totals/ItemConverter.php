<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Cart\Totals;

use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;


class ItemConverter
{
    /**
     * @var ConfigurationPool
     */
    private $configurationPool;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemInterfaceFactory
     */
    private $totalsItemFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Epicor\Comm\Helper\Configurator
     */
    private $ewaHelper;
    
    

    /**
     * Constructs a totals item converter object.
     *
     * @param ConfigurationPool $configurationPool
     * @param EventManager $eventManager
     * @param \Magento\Quote\Api\Data\TotalsItemInterfaceFactory $totalsItemFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ConfigurationPool $configurationPool,
        EventManager $eventManager,
        \Magento\Quote\Api\Data\TotalsItemInterfaceFactory $totalsItemFactory,
        \Epicor\Comm\Helper\Configurator $ewaHelper,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->configurationPool = $configurationPool;
        $this->eventManager = $eventManager;
        $this->totalsItemFactory = $totalsItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->ewaHelper = $ewaHelper;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function aroundModelToDataObject(
        \Magento\Quote\Model\Cart\Totals\ItemConverter $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        
        $itemsData = $proceed($item);
        $options = $this->getFormattedOptionValue($item);
        $itemsData->setOptions($options);            
        return $itemsData;
    }
    
    
    


    /**
     * Retrieve formatted item options view
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return string
     */
    private function getFormattedOptionValue($item)
    {
        $optionsData = [];

        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->configurationPool->getByProductType('default');

        $options = $this->configurationPool->getByProductType($item->getProductType())->getOptions($item);
        $i=0;
        foreach ($options as $index => $optionValue) {
            $params = [
                'max_length' => 55,
                'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
            ];
                        
            if (!isset($optionValue['option_type']) || substr($optionValue['option_type'], 0, 4) != 'ewa_'){
                    $optionsData[$index]['label'] = $optionValue['label'];
                    $label = $optionValue['label'];
            }else{
                    $label = '';
                    $optionValue['custom_view'] = '';
                    $params['max_length'] = false;
            }
            if (isset($optionValue['option_type']) && substr($optionValue['option_type'], 0, 4) == 'ewa_' && !($this->ewaHelper->getEwaDisplay($optionValue['option_type']))){
                   $i++;
                   continue;
            }
            $index = $index-$i;
            $option = $helper->getFormattedOptionValue($optionValue, $params);
            $optionsData[$index] = $option;
            $optionsData[$index]['label'] = $label;      
            
        }
        return \Zend_Json::encode($optionsData);
    }
    
}