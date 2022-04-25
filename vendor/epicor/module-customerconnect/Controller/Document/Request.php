<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Document;

class Request extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Preq
     */
    protected $customerconnectMessageRequestPreq;


    protected $resultPageFactory;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    protected $urlDecoder;

    protected $encryptor;


    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;


    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    protected $commHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Customerconnect\Model\Message\Request\Preq $customerconnectMessageRequestPreq,
        \Epicor\Comm\Helper\Data $commHelper
    )
    {
        $this->_localeResolver = $localeResolver;
        $this->customerconnectMessageRequestPreq = $customerconnectMessageRequestPreq;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->request = $request;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context
        );
    }

    /**
     * Controller action for Document Request
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();

        if ($data) {
            $commHelper = $this->commHelper;

            $data = $commHelper->sanitizeData($data);

            $message = $this->customerconnectMessageRequestPreq;

            $messageTypeCheck = $message->getHelper()->getMessageType('PREQ');

            if ($message->isActive() && $messageTypeCheck) {
                //validations pending
                if (false) {
                    $response = json_encode(array('message' => __('No Action selected'), 'type' => 'error'));
                } else {
                    $accountNumber = $this->getAccountNumber($data['account_number']);
                    $message->setAccountNumber($accountNumber);
                    $message->setEntityDocument($data['entity_document']);
                    $message->setEntityKey($data['entity_key']);
                    $message->setAction($data['action']);
                    $message->setEmailParams($data['email_params']);

                    if ($result = $message->sendMessage()) {
                        if(is_array($result) && $result['url'] ?? '' && $result['doc_type']?? ''){
                            $this->customerSession->unsPreqDocName();
                            $this->customerSession->setPreqDocName($data['entity_key']);
                            $response = json_encode([
                                'message' => __('Document print processed successfully'),
                                'type' => 'success',
                                'print_doc' => $result
                            ]);
                        }else{
                            $response = json_encode(array('message' => __('Document print processed successfully'), 'type' => 'success'));
                        }
                    } else {
                        $response = json_encode(array('message' => __('Failed to process document print'), 'type' => 'error'));
                        $this->messageManager->addErrorMessage(__('Failed to process document print'));
                    }
                }
            } else {
                $response = json_encode(array('message' => __('Document print option not available'), 'type' => 'error'));
                $this->messageManager->addErrorMessage(__('Document print option not available'));
            }
        } else{
            $response = json_encode(array('message' => __('No Data requested'), 'type' => 'error'));
            $this->messageManager->addErrorMessage(__('No Data requested'));
        }

        $this->getResponse()->setBody($response);
    }

    public function getAccountNumber($acctNumber)
    {
        $delimiter = $this->commHelper->getUOMSeparator();
        $parts = explode($delimiter, $acctNumber, 2);
        $acctNumber = $parts[count($parts) - 1];
        return $acctNumber;
    }

}
