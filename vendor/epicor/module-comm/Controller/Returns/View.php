<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class View extends \Epicor\Comm\Controller\Returns
{


    const FRONTEND_RESOURCE = 'Epicor_Customer::my_account_returns_details';
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
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;   



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Epicor\Comm\Model\Context $commModel,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->commHelper = $commModel->getCommHelper();
        $this->request = $commModel->getRequest();
        $this->encryptor = $commModel->getEncryptor();        
        $this->resultPageFactory = $resultPageFactory;
        $customerSession = $commModel->getCustomerSession();
        $registry = $commModel->getRegistry();
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry);
    }


public function execute()
    {
        $success = false;
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        $this->registry->register('review_display', true);
        $this->registry->register('details_display', true);

        $returnInfo = $this->request->getParam('return');
        $returnData = unserialize($this->encryptor->decrypt($helper->getUrlDecoder()->decode($returnInfo)));
        $returnId = (isset($returnData['id'])) ? $returnData['id'] : '';

        $return = $this->commCustomerReturnModelFactory->create()->load($returnId);
        /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

        if (!$return->isObjectNew()) {
            if ($return->canBeAccessedByCustomer()) {
                if ($return->getErpReturnsNumber()) {
                    $return->updateFromErp();
                }

                $this->registry->register('return_model', $return);
                $success = true;
            } else {
                $this->messageManager->addErrorMessage('You do not have permission to access this return');
            }
        } else {
            $this->messageManager->addErrorMessage('Failed to retrieve return details');
        }

        if ($success) {
            return $this->resultPageFactory->create(); 
        } else {
            session_write_close();
            $this->_redirect('*/*/index');
        }
    }

    }
