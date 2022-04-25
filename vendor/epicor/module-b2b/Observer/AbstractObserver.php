<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $b2bHelper;

    protected $captchaHelper;

    protected $customerSession;

    protected $scopeConfig;

    protected $commonAccessHelper;

    protected $frameworkHelperDataHelper;

    protected $generic;

    protected $commCustomerErpaccountFactory;

    protected $eventManager;

    protected $request;

    protected $commHelper;

    protected $storeManager;

    protected $backendJsHelper;

    protected $commonResourceAccessElementCollectionFactory;

    protected $commonAccessElementFactory;

    protected $customerUrl;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

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
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->b2bHelper = $b2bHelper;
        $this->captchaHelper = $captchaHelper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->generic = $generic;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->backendJsHelper = $backendJsHelper;
        $this->commonResourceAccessElementCollectionFactory = $commonResourceAccessElementCollectionFactory;
        $this->commonAccessElementFactory = $commonAccessElementFactory;
        $this->customerUrl = $customerUrl;
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
    }


    protected function _getCaptchaString($request, $formId)
    {
        $captchaParams = $request->getPost(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE);
        return $captchaParams[$formId];
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }


    protected function savePortalElements($data)
    {
        $elementIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['portalelements']));

        $collection = $this->commonResourceAccessElementCollectionFactory->create();
        /* @var $collection Epicor_Common_Model_Resource_Access_Element_Collection */
        $collection->addFieldToFilter('portal_excluded', 1);

        $existing = array();

        // Remove old - only if they're not passed in the data

        foreach ($collection->getItems() as $element) {
            if (!in_array($element->getId(), $elementIds)) {
                $element->setPortalExcluded(0);
                $element->save();
            } else {
                $existing[] = $element->getId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($elementIds as $elementId) {
            if (!in_array($elementId, $existing)) {
                $model = $this->commonAccessElementFactory->create()->load($elementId);
                $model->setPortalExcluded(1);
                $model->save();
            }
        }
    }

    protected function checkAvailableStoresForWebsite()
    {
        $helper = $this->commHelper;
        $stores = $helper->getSelectableStores();
        if (!$stores) {
            $this->generic->addError('No Stores available for this user on this site, unable to log in');
            //M1 > M2 Translation Begin (Rule 58)
            //$url = Mage::helper('customer')->getLogoutUrl();
            $url = $this->customerUrl->getLogoutUrl();
            //M1 > M2 Translation End
            //M1 > M2 Translation Begin (Rule p2-3)
            //Mage::app()->getResponse()->setRedirect($url);
            $this->response->setRedirect($url)->sendResponse();
            //M1 > M2 Translation End
        }
        return $stores;
    }



}

