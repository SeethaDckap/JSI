<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Orders;

use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Epicor\Common\Helper\File as CommonFile;

class Update extends \Epicor\Supplierconnect\Controller\Orders
{

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
     * @var \Epicor\Supplierconnect\Model\Message\Request\Spou
     */
    protected $supplierconnectMessageRequestSpou;
    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var CommonFile
     */
    protected $commonFileHelper;

    /**
     * Update constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Epicor\Supplierconnect\Model\Message\Request\Spou $supplierconnectMessageRequestSpou
     * @param \Magento\Framework\Url\Decoder $urlDecoder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param JsonHelper $jsonHelper
     * @param CommonFile $commonFileHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Supplierconnect\Model\Message\Request\Spou $supplierconnectMessageRequestSpou,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        JsonHelper $jsonHelper,
        CommonFile $commonFileHelper

    ) {
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->request = $request;
        $this->commHelper = $commHelper;
        $this->supplierconnectMessageRequestSpou = $supplierconnectMessageRequestSpou;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        $this->jsonHelper = $jsonHelper;
        $this->commonFileHelper = $commonFileHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Order update submit action
     */
    public function execute()
    {
        $error = false;
        $data = $this->getRequest()->getPost();
        $msgData = unserialize(base64_decode($this->getRequest()->getParam('oldData')));

        if ($data && $msgData) {
            $helper = $this->supplierconnectHelper;
            $order_requested = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($this->request->getParam('order'))));
            $erp_account_number = $this->commHelper->getSupplierAccountNumber();
            if (
                count($order_requested) == 2 &&
                $order_requested[0] == $erp_account_number &&
                !empty($order_requested[1])
            ) {
                if (isset($data['attachments'])) {
                    $this->commonFileHelper->processPageFiles('attachments', $data);
                }
                if (isset($data['lineattachments'])) {
                    $this->commonFileHelper->processPageFiles('lineattachments', $data);
                }
                $message = $this->supplierconnectMessageRequestSpou;
                $messageTypeCheck = $message->getHelper("supplierconnect/messaging")->getMessageType('SPOU');
                if ($message->isActive() && $messageTypeCheck) {
                    $message->setPurchaseOrderNumber($order_requested[1])
                        ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
                    $message->setOldPurchaseOrderData($msgData);
                    $message->setNewPurchaseOrderData($data['purchase_order']);
                    $message->setOldData($msgData);
                    $message->setNewData($data);

                    if ($message->sendMessage()) {
                        $this->messageManager->addSuccessMessage(__('Purchse Order Update Request Sent'));
                    } else {
                        $error = __('Failed to retrieve Order Details');
                    }
                } else {
                    $error = __('Order Details not available');
                }
            } else {
                $error = __('Invalid Order Number');
            }

            if ($error) {
                return  $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(array('message' => $error, 'type' => 'error'))
                );
            } else {
                $params = ['order' => $this->request->getParam('order')];
                if (!empty($data['back'])) {
                    $params['back'] = $data['back'];
                }
                $url = $this->_url->getUrl('*/*/details', $params);
                return  $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(array('redirect' => $url, 'type' => 'success'))
                );
            }

            if ($this->messageManager->getMessages()->getItems()) {
                $params = array('order' => $this->request->getParam('order'));
               if (!empty($data['back'])) {
                    $params['back'] = $data['back'];
                }
                $this->_redirect('*/*/details', $params);
            }
        }
        $params = array('order' => $this->getRequest()->getParam('order'));
        $this->_redirect('*/*/details', $params);
        return;

    }

}
