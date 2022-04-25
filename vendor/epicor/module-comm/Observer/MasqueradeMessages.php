<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Epicor\Comm\Helper\DataFactory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\UrlInterface;
use Epicor\Common\Model\Url;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * B2B, Sales Rep And Multiple CUCO
 * Frontend Display Masquerade Messages.
 */
class MasqueradeMessages implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DataFactory
     */
    private $commHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var StateInterface
     */
    private $cacheState;

    /**
     * @var SessionFactory
     */
    private $customerSessionFactory;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * MasqueradeMessages constructor.
     *
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param DataFactory          $commHelper
     * @param UrlInterface         $urlBuilder
     * @param Url                  $url
     * @param StateInterface       $state
     * @param SessionFactory       $customerSessionFactory
     * @param \Epicor\Comm\Model\CookieManager $cookieManager
     */
    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        DataFactory $commHelper,
        UrlInterface $urlBuilder,
        Url $url,
        StateInterface $state,
        SessionFactory $customerSessionFactory,
        \Epicor\Comm\Model\CookieManager $cookieManager
    )
    {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;

        $this->urlBuilder = $urlBuilder;
        $this->url = $url;
        $this->cacheState = $state;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->cookieManager = $cookieManager->getCookieManager();
        $this->cookieMetadataFactory = $cookieManager->getCookieMetadataFactory();
    }

    /**
     * Display Masquerade Messages.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $helper = $this->commHelper->create();

        $this->registry = $helper->getRegistry();
        if ($helper->isMasquerading()) {
            /* @var $request \Magento\Framework\App\Request\Http */
            $request = $observer->getEvent()->getRequest();
            $action = $request->getActionName();
            $currentUrl = $this->urlBuilder->getCurrentUrl();
            $path = $this->url->parseUrl($currentUrl)->getPath();
            $erpAccount = $helper->getErpAccountInfo();
            $error = __('You are Masquerading as %1', $erpAccount->getName());

            $isShowMessage = $this->scopeConfig->isSetFlag(
                'epicor_comm_erp_accounts/masquerade/show_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($isShowMessage) {
                $showMessage = true;
                $isFPC = $this->cacheState->isEnabled(
                    \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                );

                $params = $request->getParams() ?: [];

                $customerSession = $this->customerSessionFactory->create();
                if ($request->isPost()
                    || ($request->isAjax() && !in_array("b2bmasquerade", $params))
                    || !$isFPC
                    || $action == 'logout'
                    || $action == 'masquerade'
                    || strpos($path, 'ewacss') !== false
                    || $helper->errorExists($error)
                    || strpos($path, 'ewaedit') !== false
                    || strpos($path, 'salesrep') !== false
                    || strpos($path, 'cart') !== false
                    || strpos($path, 'customer') !== false
                    || strpos($path, 'comm/message/msq') !== false
                    || $customerSession->getIsPunchout()
                    || $this->registry->registry('set_masquerade')
                ) {
                    $showMessage = false;
                }

                if ($showMessage) {
                    $showMessage =$this->checkMessageCookies($error);
                }

                if ($showMessage) {

                    $message = $this->messageManager
                        ->createMessage(MessageInterface::TYPE_SUCCESS)
                        ->setText($error);
                    $this->messageManager->addUniqueMessages([$message]);
                    $metadata = $this->cookieMetadataFactory
                        ->createPublicCookieMetadata()
                        ->setPath('/');
                    $sectiondata = json_decode($this->cookieManager->getCookie('section_data_ids'));
                    if (!property_exists($sectiondata, 'messages')) {
                        $sectiondata->messages = 0;
                    }
                    $sectiondata->messages += 1000;
                    $this->cookieManager->setPublicCookie(
                        'section_data_ids',
                        json_encode($sectiondata),
                        $metadata
                    );

                    $this->registry->unregister('set_masquerade');
                    $this->registry->register('set_masquerade', 1);
                }

            }
        }
    }


    /**
     * Check message cookies
     *
     * @param string $error
     * @return boolean
     */
    protected function checkMessageCookies($error)
    {
        $messageCookies = $_COOKIE['mage-messages'];
        if ($messageCookies) {
            $messageCookies = json_decode($messageCookies, true);
            foreach ($messageCookies as $messageCookie) {
                if (isset($messageCookie['text']) && $error == $messageCookie['text']) {
                    return false;
                }
            }
        }
        return true;
    }

}
