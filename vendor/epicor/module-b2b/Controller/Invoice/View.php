<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Invoice;

class View extends \Epicor\B2b\Controller\Invoice
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        parent::__construct($context,$registry,$customerSession,$scopeConfig,$commHelper);
    }


/**
     * Invoice view page
     */
    public function execute()
    {
        $this->_viewPage();
    }

    }
