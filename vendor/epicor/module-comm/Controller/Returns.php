<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;


/**
 * Returns controller
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class Returns extends \Epicor\AccessRight\Controller\Action
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

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->commReturnsHelper = $commReturnsHelper;
        $this->customerSession = $customerSession;
        $this->commCustomerReturnModelFactory = $commCustomerReturnModelFactory;
        $this->generic = $generic;
        $this->registry = $registry;
        $this->jsonHelper  = $jsonHelper;
        parent::__construct(
            $context
        );
    }


    public function preDispatch()
    {
        parent::preDispatch();

        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        if (!$helper->isReturnsEnabled()) {
            $this->customerSession->addError('Returns not available');
            $this->_redirect('/');
        }
    }
/**
     * Loads the current return 
     * 
     * @return \Epicor\Comm\Model\Customer\ReturnModel
     */
    public function loadReturn($returnId = null, $updateFromErp = false, $returnObj = null)
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper Epicor_Comm_Helper_Returns */

        if ($returnObj) {
            $return = $returnObj;
        } else {
            $returnId = $this->getRequest()->getParam('return_id', $returnId);
            $return = $this->commCustomerReturnModelFactory->create();
            /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

            if (!empty($returnId)) {
                $returnId = $helper->decodeReturn($returnId);
                $return->load($returnId);
            }
        }

        if (!$return->getId()) {
           $this->messageManager->addErrorMessage('Return not found');
            $return = false;
        } else {
            if ($return->canBeAccessedByCustomer()) {
                if ($updateFromErp) {
                    $return->updateFromErp();
                }
                $this->registry->register('return_id', $returnId);
                $this->registry->register('return_model', $return);
            } else {
                $this->messageManager->addErrorMessage('You do not have permission to access this return');
                $return = false;
            }
        }

        return $return;
    }

    protected function sendStepResponse($step, $errors = array(), $return = false, $subStep = false)
    {
        $helper = $this->commReturnsHelper;
        /* @var $helper \Epicor\Comm\Helper\Returns */
        if (empty($errors)) {
            $result = $helper->getNextReturnsStep($step);
        } else {
            if (!is_array($errors)) {
                $errors = array($errors);
            }
            $step = $subStep ? $subStep : null;
            //only add tab entry if subStep is set (currently only for lines)
            $result = array('errors' => $errors);
            if ($subStep) {
                $result['tab'] = $step;
            }
        }

        if ($return) {
            return $this->jsonHelper->jsonEncode($result);
        } else {
            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
        }
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        $expired = false;

        $session = $this->customerSession;
        /* @var $session Mage_Customer_Model_Session */

        if (!$session->isLoggedIn()) {
            $name = $session->getReturnGuestName();
            $email = $session->getReturnGuestEmail();

            if (empty($name) || empty($email)) {
                $expired = true;
            }
        }

        if ($expired) {
            $this->_ajaxRedirectResponse();
        }

        return $expired;
    }

    /**
     * Send Ajax redirect response
     *
     * @return Mage_Checkout_OnepageController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }
}
