<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

class CheckB2bUserCreate extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

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
        \Magento\Framework\App\Cache\StateInterface $state,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ActionFlag $actionFlag)
    {
        $this->messageManager = $messageManager;
        $this->_actionFlag = $actionFlag;
        parent::__construct($b2bHelper, $captchaHelper, $customerSession, $scopeConfig, $commonAccessHelper, $frameworkHelperDataHelper, $generic, $commCustomerErpaccountFactory, $eventManager, $request, $commHelper, $storeManager, $backendJsHelper, $commonResourceAccessElementCollectionFactory, $commonAccessElementFactory, $customerUrl, $response, $urlBuilder);
    }

    /**
     * Check Captcha On Register User Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return Mage_Captcha_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'b2b_create';
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            if (!$captchaModel->isCorrect($this->_getCaptchaString($controller->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->customerSession->setCustomerFormData($controller->getRequest()->getPostValue());
                $controller->getResponse()->setRedirect($this->urlBuilder->getUrl('*/*/register'));
            }
        }

        return $this;
    }

}