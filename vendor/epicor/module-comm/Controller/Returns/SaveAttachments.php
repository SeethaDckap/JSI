<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

use Epicor\Comm\Model\Customer\ReturnModel\NewFileAttachments as AttachmentFile;

class SaveAttachments extends \Epicor\Comm\Controller\Returns
{


    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    private $attachmentFile;


    public function __construct(
        AttachmentFile $attachmentFile,
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper  = $jsonHelper;
        $this->attachmentFile = $attachmentFile;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry
        );
    }


    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $helper = $this->commReturnsHelper;
            /* @var $helper \Epicor\Comm\Helper\Returns */
            /* Do action stuff here */
            $errors = array();
            $return = $this->loadReturn();

            if (!$return->isObjectNew()) {
                if ($return->isActionAllowed('Attachments')) {
                    $attachmentsPost = $this->getRequest()->getParam('attachments', false);
                    $errors = array_merge($errors, $this->attachmentFile->getFileErrors());
                    if ($this->attachmentFile->isValidAttachments()) {
                        $helper->processPostedAttachments($return, $attachmentsPost, 'attachments');
                    }
                }
            } else {
                $errors[] = __('Failed to find return to add attachments to. Please try again.');
            }
            $subStep = !empty($errors) ? 'attachments' : false;
            $this->registry->register('response_json', $this->sendStepResponse('attachments', $errors, true, $subStep));
            $resultPage = $this->resultPageFactory->create();
            $this->getResponse()->setBody(
                $resultPage->getLayout()->createBlock('Epicor\Comm\Block\Customer\Returns\Iframeresponse')->toHtml()
            );
        }
    }
}
