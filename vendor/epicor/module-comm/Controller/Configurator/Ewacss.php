<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

class Ewacss extends \Epicor\Comm\Controller\Configurator
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $this->getResponse()->setHeader('Content-type', 'text/css', true);
        echo $this->scopeConfig->getValue('epicor_comm_enabled_messages/cim_request/ewa_css', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    }
