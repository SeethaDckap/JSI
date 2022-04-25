<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\RecentPurchases;

class Reorder extends \Epicor\Customerconnect\Controller\RecentPurchases
{

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    private $urlDecoder;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Epicor\Customerconnect\Helper\Recentpurchases
     */
    private $recentpurchasesHelper;

    /**
     * Reorder constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Url\Decoder $urlDecoder
     * @param Epicor\Customerconnect\Helper\Recentpurchases $recentpurchasesHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Epicor\Customerconnect\Helper\Recentpurchases $recentpurchasesHelper

    )
    {
        $this->urlDecoder = $urlDecoder;
        $this->recentpurchasesHelper = $recentpurchasesHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $result = null;
        if ($this->getRequest()->getParam('massaction')) {

            $order = $this->recentpurchasesHelper->processMassaction($this->getRequest()->getPost());
            $result = $this->recentpurchasesHelper->processReorder($order);

        } else {
            $recentpurchsesItem = $this->getRequest()->getParam('recentpurchaseitem');
            if ($recentpurchsesItem) {
                $recentpurchasesItem = $this->urlDecoder->decode($this->getRequest()->getParam('recentpurchaseitem'));
                $order = $this->recentpurchasesHelper->processSingleItem($recentpurchasesItem, $this->getRequest()->getParam('recentpurchasesorderqty'));
                $result = $this->recentpurchasesHelper->processReorder($order);
            }
        }
        if ($result == 'error') {
            return $this->_redirect('customerconnect/recentpurchases');
        }
        return $this->_redirect('checkout/cart/');
    }
}
