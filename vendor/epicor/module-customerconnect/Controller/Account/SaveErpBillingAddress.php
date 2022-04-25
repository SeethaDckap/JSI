<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Account;

class SaveErpBillingAddress extends \Epicor\Customerconnect\Controller\Account
{

    /**
     * @var
     */
    protected $_resourceConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    )
    {
        $this->_resourceConfig = $resourceConfig;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache
        );
    }

    public function execute($data = null)
    {

        $form_data = json_decode($this->getRequest()->getParam('json_form_data'), true);

        $commHelper = $this->commHelper;
        $erpAccountInfo = $commHelper->getErpAccountInfo();
        $erpCode = $erpAccountInfo->getCompany() . "_" . $erpAccountInfo->getShortCode();

        $this->_resourceConfig->saveConfig("Epicor_Comm/save_new_addresses/{$erpCode}", $form_data['new_address_values'], 'stores', 0);
        $this->_resourceConfig->saveConfig("Epicor_Comm/save_new_addresses/erp_save_billing_{$erpCode}", $form_data['save_billing'], 'stores', 0);

        $this->cache->clean(array('CONFIG', 'LAYOUT_GENERAL_CACHE_TAG'));
    }
}
