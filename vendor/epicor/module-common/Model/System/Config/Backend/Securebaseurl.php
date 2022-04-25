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
class Securebaseurl extends \Magento\Config\Model\Config\Backend\Baseurl
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
    }
    /**
     * Send SYN message if value is changed
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {

            $groups = $this->getGroups();

            $useInFrontend = isset($groups['secure']['fields']['use_in_frontend']['value']) ? $groups['secure']['fields']['use_in_frontend']['value'] : $this->scopeConfig->isSetFlag('web/secure/use_in_frontend', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if ($this->getField() == 'base_url' && $useInFrontend) {
                if (!$this->registry->registry('sending_url_change_syn')) {
                    $this->eventManager->dispatch('system_config_base_url_changed', array(
                        'url' => $this->getValue()
                    ));
                    $this->registry->register('sending_url_change_syn', true);
                }
            }
        }

        parent::afterSave();
    }

}
