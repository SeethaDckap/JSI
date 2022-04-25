<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Rfq;

class Update extends \Epicor\Supplierconnect\Controller\Rfq
{
    /**
     * @var \Epicor\Supplierconnect\Model\Message\Request\Suru
     */
    protected $supplierconnectMessageRequestSuru;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Epicor\Common\Helper\File
     */
    protected $commonFileHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Supplierconnect\Model\Message\Request\Suru $supplierconnectMessageRequestSuru,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Common\Helper\File $commonFileHelper,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectHelper
    ) {
        $this->supplierconnectMessageRequestSuru = $supplierconnectMessageRequestSuru;
        $this->jsonHelper = $jsonHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->commonFileHelper = $commonFileHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commMessagingHelper,
            $generic,
            $request,
            $commHelper,
            $registry,
            $commonAccessHelper
        );
    }
    public function execute()
    {
        $error = false;
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor\Comm\Helper\Messaging */
        if ($newData = $this->getRequest()->getPost()) {

            if(isset($newData['old_data'])){
                $oldData = unserialize(base64_decode($newData['old_data']));
                unset($newData['old_data']);
            }else{
                $oldData = [];
            }
            $suru = $this->supplierconnectMessageRequestSuru;
            /* @var $suru Epicor\Supplierconnect\Model\Message\Request\Suru */

            if ($suru->isActive() && $helper->getMessageType('SURU')) {
                $aFiles = array();
                if (isset($newData['attachments'])) {
                    $aFiles = $this->commonFileHelper->processPageFiles('attachments', $newData);
                }
                $suru->setRfqNumber($oldData['rfq_number']);
                $suru->setLine($oldData['line']);
                $suru->setOldData($oldData);
                $suru->setNewData($newData);

                if ($suru->sendMessage()) {
                    $rfq = $suru->getResults();
                    $this->messageManager->addSuccessMessage(__('RFQ update request sent successfully'));
                  //  $this->customerconnectHelper->processCrquFilesSuccess($aFiles, $rfq);
                } else {
                    $error = __('RFQ update request failed : %1', $suru->getStatusDescription());
                }
            } else {
                $error = __('RFQ update not available');
            }
        } else {
            $error = __('No Data Sent');
        }

        if ($error) {
            return  $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(array('message' => $error, 'type' => 'error'))
                );
        } else {
            $rfq = [
                $helper->getSupplierAccountNumber(),
                $oldData['rfq_number'],
                $oldData['line']
            ];
            $rfq_requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($rfq)));
            $url = $this->_url->getUrl('*/*/details', array('rfq' => $rfq_requested));
            return  $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(array('redirect' => $url, 'type' => 'success'))
            );
        }
    }

}
