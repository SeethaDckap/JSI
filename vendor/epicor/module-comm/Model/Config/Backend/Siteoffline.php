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
class Siteoffline extends \Magento\Framework\App\Config\Value
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
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailTemplateFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,   
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Epicor\B2b\Controller\Context $epicorContext,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $epicorContext->getStoreManager();
        $this->scopeConfig = $epicorContext->getScopeConfig();
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->transportBuilder = $epicorContext->getTransportBuilder();
        $this->commHelperFactory = $epicorContext->getCommHelperFactory();
        $this->registry = $epicorContext->getRegistry();
        $registry = $epicorContext->getRegistry();
        parent::__construct(
            $context,
            $registry,
            $this->scopeConfig,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function beforeSave()
    {
        if ($this->isValueChanged() && in_array($this->getValue(), array('0', 0 ,null)) && !$this->registry->registry('site_offline')) {            
            $this->registry->register('site_offline', true);
            $this->commHelperFactory->create()->sendEmailWhenSiteOffline();
        }
        parent::beforeSave();
    }

}
