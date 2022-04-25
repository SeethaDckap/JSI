<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Shipments;

class Reorder extends \Epicor\Customerconnect\Controller\Shipments
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
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Cusd $customerconnectMessageRequestCusd,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
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
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCusd,
            $registry,
            $generic,
            $encryptor,
            $urlDecoder
        );
    }

    public function execute()
    {
        $shipment = false;
        if ($this->_loadShipment()) {
            $shipment = $this->registry->registry('customer_connect_shipments_details');
        }

        $helper = $this->commonCartHelper;

        if (empty($shipment) || !$helper->processReorder($shipment)) {
            if (!$this->messageManager->getMessages()->getItems()) {
                $this->messageManager->addErrorMessage('Failed to build cart for Re-Order request');
            }

            $this->_redirect('checkout/cart/');

            $location = $this->urlDecoder->decode($this->request->getParam('return'));

            $result = $this->resultRedirectFactory->create();
            $result->setUrl($location);
            return $result;
        } else {
            $this->_redirect('checkout/cart/');
        }
    }

}
