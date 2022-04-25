<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Request;

class Test extends \Epicor\Quotes\Controller\Request
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Quotes\Model\CronFactory
     */
    protected $quotesCronFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,    
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Quotes\Model\CronFactory $quotesCronFactory
    ) {
        $this->quotesCronFactory = $quotesCronFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context
           ,$customerSession     
        );
    }



    public function execute()
    {
        $cron = $this->quotesCronFactory->create();

        $cron->checkedExpired();
    }

    }
