<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Orders;

class Reorder extends \Epicor\Customerconnect\Controller\Orders
{

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper
    )
    {
        $this->commonCartHelper = $commonCartHelper;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $request,
            $customerconnectHelper,
            $registry,
            $generic,
            $urlDecoder,
            $encryptor
        );
    }

    public function execute()
    {
        $order = false;
        if ($this->_getOrderDetails()) {
            $order = $this->registry->registry('customer_connect_order_details');
        }

        $helper = $this->commonCartHelper;

        if (empty($order) || !$helper->processReorder($order)) {
            if (!$this->messageManager->getMessages()->getItems()) {
                $this->messageManager->addErrorMessage(__('Failed to build cart for Re-Order request'));
            }

            $location = $this->urlDecoder->decode($this->getRequest()->getParam('return'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setUrl($location);
        } else {
            return $this->_redirect('checkout/cart/');
        }
    }

}
