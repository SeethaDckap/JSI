<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller\SetupRequest;

use Epicor\Comm\Model\Customer\Erpaccount;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\Action\Context;
use Epicor\Punchout\Model\TokenGenerator\JwtManagement;
use Magento\Customer\Model\CustomerFactory;
use Epicor\Punchout\Model\Request\JwtPayloadValidatorInterface;

/**
 * Class SessionStart
 *
 * @package Epicor\Punchout\Controller\SetupRequest
 */
class SessionStart extends Action
{

    const HOME_PAGE = 'checkout/cart/';

    /**
     * Jwt Management.
     *
     * @var JwtManagement
     */
    private $jwtManagement;

    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Customer Model.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerModel;

    /**
     * Token Validator
     *
     * @var JwtPayloadValidatorInterface
     */
    private $tokenValidator;

    /**
     * Data
     *
     * @var array
     */
    private $data;


    /**
     * SessionStart constructor.
     *
     * @param \Magento\Framework\App\Action\Context   $context        Context.
     * @param \Magento\Framework\Data\Form\FormKey    $formKey        FormKey.
     * @param \Magento\Framework\App\Request\Http     $request        Request.
     * @param JwtManagement                           $jwtManagement  JwtManagement.
     * @param JwtPayloadValidatorInterface            $tokenValidator TokenValidator.
     * @param \Magento\Customer\Model\CustomerFactory $customerModel  CustomerModel.
     * @param array                                   $data           Data.
     *
     * @throws \Magento\Framework\Exception\LocalizedException LocalizedException.
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        Http $request,
        JwtManagement $jwtManagement,
        JwtPayloadValidatorInterface $tokenValidator,
        CustomerFactory $customerModel,
        array $data=[]
    ) {
        $this->request        = $request;
        $this->formKey        = $formKey;
        $this->jwtManagement  = $jwtManagement;
        $this->customerModel  = $customerModel;
        $this->tokenValidator = $tokenValidator;
        $this->data           = $data;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
        parent::__construct(
            $context
        );

    }//end __construct()


    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $tokenId = $this->request->getParam('tokenid');
        if (empty($tokenId)) {
            $this->data['messageManager']->addErrorMessage(__('Empty Token.'));
            $this->_redirect(self::HOME_PAGE);
            return;
        }
        $jwtPayload = $this->jwtManagement->decode($tokenId, $this->data['config']->getApiKey());
        if ($this->tokenValidator->validate($jwtPayload) || $jwtPayload['error']) {
            $msg = 'Token Expired!!';
            if ($jwtPayload['error']) {
                $msg = $jwtPayload['error_message'];
            }

            $this->data['messageManager']->addErrorMessage(__($msg));
            $this->_redirect(self::HOME_PAGE);

            return;
        }
        if ($this->data['customerSessionObj']->getCustomer()->getId() !== $jwtPayload['customer_id']) {
            $this->data['customerSessionObj']->logout();
        }

        if (($this->data['customerSessionObj']->isLoggedIn()  && $this->data['customerSessionObj']->getCustomer()->getId() === $jwtPayload['customer_id']) && $this->data['customerSessionObj']->getIsPunchout()) {
            $this->data['messageManager']->addWarning(__('Punchout session already exists'));
            $this->_redirect(self::HOME_PAGE);
            return;
        }

        if (!empty($jwtPayload['customer_id'])) {
            $this->initiatePunchoutLogin($jwtPayload);
        }//end if

    }//end execute()


    /**
     * Set data in punchout session.
     *
     * @param array $jwtPayload JWT Token Payload Data.
     * @param $customer
     */
    public function setPunchoutDataInSession(array $jwtPayload, $customer)
    {
        $cartId = unserialize(base64_decode($this->getRequest()->getParam('cartId')));

        $erpAccount = $this->getErpAccount($jwtPayload['connection_id']);
        $erpAccts   = $customer->getErpAcctCounts();
        if (is_array($erpAccts) && count($erpAccts) > 1) {
            $this->data['customerSessionObj']->setMasqueradeAccountId($erpAccount->getId());
        }

        $this->data['customerSessionObj']->setIsPunchout($jwtPayload['is_punchout']);
        $this->data['customerSessionObj']->setBuyerCookie($jwtPayload['buyer_cookie']);
        $this->data['customerSessionObj']->setConnectionId($jwtPayload['connection_id']);
        $this->data['customerSessionObj']->setPostUrl($jwtPayload['post_url']);
        $this->data['customerSessionObj']->setPunchoutCartId($cartId);

    }//end setPunchoutDataInSession()


    /**
     * Load punchout cart.
     *
     * @param int $cartId Cart ID
     */
    public function loadPunchoutCart(int $cartId)
    {
        $this->data['cartObj']->loadCart($cartId);
        $errorIds = base64_decode($this->getRequest()->getParam('errorIds'));

        if (!empty($errorIds)) {
            $this->data['messageManager']->addErrorMessage(
                    __('Following product(s) needs to be configured.'.$errorIds)
            );
        }

    }//end loadPunchoutCart()


    /**
     * Initiate punchout login.
     *
     * @param array $jwtPayload Payload data.
     */
    public function initiatePunchoutLogin($jwtPayload)
    {
        $customer = $this->customerModel->create()->load($jwtPayload['customer_id']);
        if (!empty($customer->getData())) {
            //it triggers msq and bsv when Guest cart get merged to punchout session
            $quote  = $this->data['checkoutSessionObj']->getQuote();
            $registryObj = $this->data['registryObj'];
            if ($quote->getId()) {
                $registryObj->register('msq_sent', true);
                if (!$registryObj->registry('bsv_sent')) {
                    $registryObj->register('bsv_sent', true);
                }
                $quote->setIsActive(0)->save();
            }
            $this->setPunchoutDataInSession($jwtPayload, $customer);
            $registryObj->unregister('epicor_lists_active_lists');
            $this->data['customerSessionObj']->setCustomerAsLoggedIn($customer);
            $this->data['punchoutSessionObj']->initCustomerSection();
            $this->data['customerSessionObj']->setDisplayLocations(false);
            // Get cart Id from url.
            $cartId = unserialize(base64_decode($this->getRequest()->getParam('cartId')));
            if (!empty($cartId)) {
                $registryObj->unregister('msq_sent');
                $registryObj->unregister('bsv_sent');
                $registryObj->unregister('dont_send_bsv');
                $this->data['customerSessionObj']->setBsvTriggerTotals(array());
                $this->loadPunchoutCart($cartId);
            }
            $this->data['messageManager']->addSuccessMessage(__('Punchout session activated'));
        } else {
            $this->data['messageManager']->addErrorMessage(__('Customer does not exist'));
        }//end if

        $this->_redirect(self::HOME_PAGE);

    }//end initiatePunchoutLogin()


    /**
     * Get ERP Account.
     *
     * @param $connectionId
     *
     * @return Erpaccount
     */
    public function getErpAccount($connectionId)
    {
        $connection = $this->data['connectionRepository']->getById($connectionId);
        $identity   = $connection->getIdentity();

        return $this->data['commHelper']->getErpAccountByAccountNumber($identity);

    }//end getErpAccount()


}//end class
