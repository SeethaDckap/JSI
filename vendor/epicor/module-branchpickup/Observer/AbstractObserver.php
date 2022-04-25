<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    
    protected $getSelectedBranch = null;

    protected $_helper;

    protected $_helperBranch;

    protected $_branchModel;

    protected $branchPickupHelper;

    protected $branchPickupBranchpickupHelper;

    protected $branchPickupBranchpickupFactory;

    protected $checkoutSession;

    protected $request;

    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        \Epicor\BranchPickup\Model\BranchpickupFactory $branchPickupBranchpickupFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->branchPickupHelper = $branchPickupHelper;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->branchPickupBranchpickupFactory = $branchPickupBranchpickupFactory;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->_helper = $this->branchPickupHelper;
        $this->_helperBranch = $this->branchPickupBranchpickupHelper;
        $this->_branchModel = $this->branchPickupBranchpickupFactory->create();
        $this->response = $response;
        $this->urlDecoder = $urlDecoder;
        $this->escaper = $escaper;
        $this->messageManager = $messageManager;
    }



    protected function getRefererUrl()
    {
        $request = $this->request;
        $refererUrl = $request->getServer('HTTP_REFERER');
        if ($url = $request->getParam(\Magento\Framework\App\Response\RedirectInterface::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $request->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_BASE64_URL)) {
            //M1 > M2 Translation Begin (Rule p2-7)
            //$refererUrl = Mage::helper('core')->urlDecode($url);
            $refererUrl = $this->urlDecoder->decode($url);
            //M1 > M2 Translation End

        }
        if ($url = $request->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED)) {
            //M1 > M2 Translation Begin (Rule p2-7)
            //$refererUrl = Mage::helper('core')->urlDecode($url);
            $refererUrl = $this->urlDecoder->decode($url);
            //M1 > M2 Translation End
        }
        //M1 > M2 Translation Begin (Rule p2-7)
        //$refererUrl = Mage::helper('core')->escapeUrl($refererUrl);
        $refererUrl = $this->escaper->escapeUrl($refererUrl);
        //M1 > M2 Translation End

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = $this->storeManager->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }

    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            if ((strpos($url, $this->storeManager->getStore()->getBaseUrl()) === 0) || (strpos($url, $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, true)) === 0)) {
                return true;
            }
        }
        return false;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }




}

