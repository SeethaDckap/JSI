<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Backend;


/**
 * Default Erp account backend controller
 * 
 * Updates the Default ERP code if the Erp account changes
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Locations extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
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
        $this->resourceConfig = $resourceConfig;
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
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $locationsAvailable = (int) $this->getValue();

            //set the branchpickup allowed marker to false if locations not allowed
            if (!$locationsAvailable) {
                $scope = ($this->getScopeId()) ? 'stores' : 'default';
                $this->resourceConfig->saveConfig('epicor_comm_locations/global/isbranchpickupallowed', 0, $scope, $this->getScopeId());
                $this->cache->clean(array('config', 'layout'));
            }
        }
        return parent::afterSave();
    }

}
