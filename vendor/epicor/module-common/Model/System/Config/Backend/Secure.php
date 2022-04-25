<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\System\Config\Backend;


/**
 * URL backend model override
 * 
 * Adds a hook into base url changes
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Secure extends \Magento\Config\Model\Config\Backend\Secure
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\View\Asset\MergeService $mergeService,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->eventManager = $context->getEventDispatcher();
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $mergeService,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Send SYN message if value is changed
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            if ($this->getField() == 'use_in_frontend') {

                $groups = $this->getGroups();

                $secure = isset($groups['secure']['fields']['base_url']['value']) ? $groups['secure']['fields']['base_url']['value'] : $this->scopeConfig->getValue('web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $unsecure = isset($groups['unsecure']['fields']['base_url']['value']) ? $groups['unsecure']['fields']['base_url']['value'] : $this->scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                if ($secure != $unsecure) {
                    if ($this->getValue() == '1') {
                        $url = $secure;
                    } else {
                        $url = $unsecure;
                    }
                    if (!$this->registry->registry('sending_url_change_syn')) {
                        $this->eventManager->dispatch('system_config_base_url_changed', array(
                            'url' => $url
                        ));
                        $this->registry->register('sending_url_change_syn', true);
                    }
                }
            }
        }

        return parent::afterSave();
    }

}
