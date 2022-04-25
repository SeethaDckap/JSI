<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

class SaveAdminAccessRights extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;




public function __construct(
    \Epicor\B2b\Helper\Data $b2bHelper,
    \Magento\Captcha\Helper\Data $captchaHelper,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Epicor\Common\Helper\Access $commonAccessHelper,
    \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
    \Magento\Framework\Session\Generic $generic,
    \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
    \Magento\Framework\Event\ManagerInterface $eventManager,
    \Magento\Framework\App\Request\Http $request,
    \Epicor\Comm\Helper\Data $commHelper,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Backend\Helper\Js $backendJsHelper,
    \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory $commonResourceAccessElementCollectionFactory,
    \Epicor\Common\Model\Access\ElementFactory $commonAccessElementFactory,
    \Magento\Customer\Model\Url $customerUrl,
    \Magento\Framework\App\ResponseInterface $response,
    \Magento\Framework\UrlInterface $urlBuilder,
    \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $state)
{
    $this->cache = $cache;
        $this->_cacheState = $state;
    parent::__construct($b2bHelper, $captchaHelper, $customerSession, $scopeConfig, $commonAccessHelper, $frameworkHelperDataHelper, $generic, $commCustomerErpaccountFactory, $eventManager, $request, $commHelper, $storeManager, $backendJsHelper, $commonResourceAccessElementCollectionFactory, $commonAccessElementFactory, $customerUrl, $response, $urlBuilder);
}

    /**
     * Get Captcha String
     *
     * @param \Magento\Framework\DataObject $request
     * @param string $formId
     * @return string
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();

        if ($data = $request->getPost()) {
            if (isset($data['portal_element_excluded'])) {
                $this->savePortalElements($data);
                //M1 > M2 Translation Begin (Rule 12)
                //if (Mage::app()->useCache('access')) {
                if ($this->_cacheState->isEnabled('access')) {
                    //$cache = Mage::app()->getCacheInstance();
                    /* @var $cache Mage_Core_Model_Cache */
                    //$cache->clean(array('PORTAL_EXCLUSIONS'));
                    $this->cache->clean(array('PORTAL_EXCLUSIONS'));
                    //M1 > M2 Translation End
                }
            }
            return;
        }
    }

}