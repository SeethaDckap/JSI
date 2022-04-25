<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Portal;

class Error extends \Epicor\B2b\Controller\Portal
{

    public function __construct(
        \Epicor\B2b\Controller\Context $context,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\Translate\Inline\StateInterface $translateInterface,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Model\Message\Request\CncFactory $commMessageRequestCncFactory,
        \Magento\Customer\Model\FormFactory $customerFormFactory,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        parent::__construct($context, $directoryRegionFactory, $translateInterface, $emailTemplateFactory, $commCustomerErpaccountFactory, $customerCustomerFactory, $commMessageRequestCncFactory, $customerFormFactory, $customerAddressFactory,  $cache, $state, $customerUrl, $subscriberFactory);
    }


    public function execute()
    {
        $errormsg = $this->registry->registry('b2bregerrormsg');

        $this->messageManager->addErrorMessage($errormsg);
    }

}
