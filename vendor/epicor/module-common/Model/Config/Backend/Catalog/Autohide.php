<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Backend\Catalog;


/**
 *
 * Autohide backend model
 *
 * Used to clear cache when the catalog category authoide functionality is turned on and off
 *
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 *
 */
class Autohide extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cache = $context->getCacheManager();
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


    /**
     * Process object after save data
     *
     * @return \Epicor\Common\Model\Config\Backend\Cert
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            //M1 > M2 Translation Begin (Rule p2-6.7)
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->cache;
            //M1 > M2 Translation End
            /* @var $cache Mage_Core_Model_Cache */
            $cache->clean(array('CATALOG_NAVIGATION_HTML_CACHE'));
        }

        return parent::afterSave();
    }

}
