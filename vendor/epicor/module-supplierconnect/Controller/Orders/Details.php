<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 * =======
 */

namespace Epicor\Supplierconnect\Controller\Orders;

class Details extends \Epicor\Supplierconnect\Controller\Orders
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_orders_details';
    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spod
     */
    protected $supplierconnectMessageRequestSpod;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Supplierconnect\Model\Message\Request\Spod $supplierconnectMessageRequestSpod,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    )
    {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->supplierconnectMessageRequestSpod = $supplierconnectMessageRequestSpod;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }

    /**
     * Order details page
     */
    public function execute()
    {
        $helper = $this->supplierconnectHelper;
        $order_requested = explode(
            ']:[',
            $this->encryptor->decrypt(
                $this->urlDecoder->decode(
                    $this->request->getParam('order')
                )
            )
        );

        $erp_account_number = $this->commHelper->getSupplierAccountNumber();
        $resultPage = $this->resultPageFactory->create();
        if (
            count($order_requested) == 2 &&
            $order_requested[0] == $erp_account_number &&
            !empty($order_requested[1])
        ) {
            $message = $this->supplierconnectMessageRequestSpod;

            $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPOD');

            if ($message->isActive() && $messageTypeCheck) {
                $message
                    ->setPurchaseOrderNumber($order_requested[1])
                    ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));


                if ($message->sendMessage()) {

                    $purchaseOrder = $message->getResults();

                    $this->registry->register('supplier_connect_order_details', $purchaseOrder);
//                    Mage::getSingleton('core/session')->setLastPurchaseOrder($purchaseOrder);

                    if ($purchaseOrder->getPurchaseOrder()) {
                        if ($purchaseOrder->getPurchaseOrder()->getOrderConfirmed() != '') {
                            $this->registry->register('supplier_connect_order_display', 'edit');
                        } else {
                            $this->registry->register('supplier_connect_order_display', 'view');
                        }

                        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
                        if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
                            $pageMainTitle->setPageTitle(__('Purchase Order : %1', $purchaseOrder->getPurchaseOrder()->getPurchaseOrderNumber()));
                        }
                    }

                    $accessHelper = $this->commonAccessHelper;
                    if (!$accessHelper->customerHasAccess('Epicor_Supplierconnect', 'Orders', 'update', '', 'Access')) {
                        $this->registry->unregister('supplier_connect_order_display');
                        $this->registry->register('supplier_connect_order_display', 'view');
                    }
                    return $resultPage;
                } else {
                    $this->messageManager->addErrorMessage(__('Failed to retrieve Order Details'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Order Details not available'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid Order Number'));
        }

        if ($this->messageManager->getMessages()->getItems()) {
            $this->_redirect('*/*/index');
        }
    }


}