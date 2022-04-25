<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Config\Backend;


/*
 * This class will check if the ERP system in the config has changed. If it has, it will
 * load up the default message active values present in the xml
 */

class Erps extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->resourceConfig = $resourceConfig;
        $this->commonHelper = $commonHelper;
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


    public function beforeSave()
    {
        $newErpValue = $this->getValue();

        //M1 > M2 Translation Begin (Rule p2-1)
        /*$OldErpValue = Mage::getModel('core/config_data')->addFieldToFilter('path', array('like' => '%licensing/erp%'))
            ->addFieldToFilter('scope_id', array('eq' => $this->getScopeId()))
            ->getFirstItem()
            ->getValue();*/

        $OldErpValue = $this->getCollection()->addFieldToFilter('path', array('like' => '%licensing/erp%'))
            ->addFieldToFilter('scope_id', array('eq' => $this->getScopeId()))
            ->getFirstItem()
            ->getValue();
        //M1 > M2 Translation End
        if ($newErpValue != $OldErpValue) {
            if (!$this->registry->registry('newErpValue')) {
                $this->registry->register('newErpValue', true);
                $this->registry->register('newErpValueName', $newErpValue);
                $this->registry->register('newErpValueScopeId', $this->getScopeId());
            }
        }
        parent::beforeSave();
    }

    public function afterSave()
    {
        $this->_afterSave(false, false);
        //M1 > M2 Translation Begin (Rule 64)
        return $this;
        //M1 > M2 Translation End
    }

    public function _afterSave($mapping_data = false, $unRegisterKeys = true)
    {
        if ($this->registry->registry('newErpValue')) {
            $helper = $this->commonHelper;
            $helper->setErpDefaults($this->registry->registry('newErpValueName'), $this->registry->registry('newErpValueScopeId'), $mapping_data);
            if ($unRegisterKeys) {
                //M1 > M2 Translation Begin (Rule p2-8)
                /*Mage::unRegister('newErpValue');
                Mage::unRegister('newErpValueName');
                Mage::unRegister('newErpValueScopeId');*/
                $this->registry->unRegister('newErpValue');
                $this->registry->unRegister('newErpValueName');
                $this->registry->unRegister('newErpValueScopeId');
                //M1 > M2 Translation End
            }
        }
    }

}
