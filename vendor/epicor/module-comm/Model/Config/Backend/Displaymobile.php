<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Backend;


/**
 * Mobile Display backend model
 * 
 * Updates the Mobile Required setting if the value changes & is set to hide
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Displaymobile extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cache = $context->getCacheManager();
        $this->resourceConfig=$resourceConfig;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $show = $this->getValue();

            if (empty($show)) {
                $scope = ($this->getScopeId()) ? 'stores' : 'default';
                //M1 > M2 Translation Begin (Rule P2-2)
                // Mage::getConfig()->saveConfig('checkout/options/mobile_number_required', false, $scope, $this->getScopeId());
                 $this->resourceConfig->saveConfig('checkout/options/mobile_number_required', false, $scope, $this->getScopeId());
                //M1 > M2 Translation End

                $this->cache->clean(array('CONFIG', 'LAYOUT_GENERAL_CACHE_TAG'));
            }
        }
        return parent::afterSave();
    }

}
