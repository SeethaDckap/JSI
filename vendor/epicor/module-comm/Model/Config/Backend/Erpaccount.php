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
class Erpaccount extends \Magento\Framework\App\Config\Value
{

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
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
            $defaultErpAccountId = $this->getValue();

            if ($defaultErpAccountId) {
                $erpAccount = $this->commCustomerErpaccountFactory->create()->load($defaultErpAccountId);
                $scope = ($this->getScopeId()) ? $this->getScope() : 'default';
                //M1 > M2 Translation Begin (Rule P2-2)
                //Mage::getConfig()->saveConfig('customer/create_account/qs_default_erpaccount', $erpAccount->getShortCode(), $scope, $this->getScopeId());
                $this->resourceConfig->saveConfig('customer/create_account/qs_default_erpaccount', $erpAccount->getShortCode(), $scope, $this->getScopeId());
                //M1 > M2 Translation End
                $this->_cacheManager->clean(array('CONFIG', 'LAYOUT_GENERAL_CACHE_TAG'));
            }
        }
        return parent::afterSave();
    }

}
