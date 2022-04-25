<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class CreateReturn extends \Epicor\Comm\Controller\Returns
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
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
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
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $helper = $this->commReturnsHelper;
            /* @var $helper Epicor_Comm_Helper_Returns */

            $errors = array();
            $cusRef = $this->getRequest()->getParam('customer_ref', false);
            $caseNum = $this->getRequest()->getParam('case_number', false);

            if ($cusRef === false) {
                $errors[] = __('Customer Reference Empty');
            } else {

                $return = $helper->findReturn('customer_ref', $cusRef);

                if ($return['found']) {
                    $errors[] = __('A return already exists with the supplied customer reference');
                } else {

                    $return = $this->commCustomerReturnModelFactory->create();
                    /* @var $return Epicor_Comm_Model_Customer_ReturnModel */

                    if (!empty($caseNum)) {
                        $caseInfo = $helper->findCase($caseNum);

                        if (!$caseInfo['valid']) {
                            $errors[] = __('Not a valid Case Number');
                        } else if (!empty($caseInfo['erp_return_number'])) {
                            $errors[] = __('A return already exists with the supplied Case Number');
                        } else {
                            $return->setRmaCaseNumber($caseNum);
                        }
                    }

                    $guestEmail = $this->getRequest()->getParam('guest_email', false);
                    $guestName = $this->getRequest()->getParam('guest_name', false);

                    if (!empty($guestName)) {
                        $guestName = $helper->decodeReturn($guestName);
                        $return->setCustomerName($guestName);
                        $this->registry->register('guest_name', $guestName);
                    }

                    if (!empty($guestEmail)) {
                        $guestEmail = $helper->decodeReturn($guestEmail);
                        $return->setEmailAddress($guestEmail);
                        $this->registry->register('guest_email', $guestEmail);
                    }

                    if (empty($guestName) && empty($guestEmail)) {
                        // set customer info here
                        $customer = $this->customerSession->getCustomer();
                        /* @var $customer Epicor_Comm_Model_Customer */

                        $commHelper = $this->commHelper;
                        /* @var $commHelper Epicor_Comm_Helper_Data */
                        $erpAccount = $commHelper->getErpAccountInfo();
                        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

                        $return->setErpAccountId($erpAccount->getId());
                        $return->setIsGlobal(0);
                        $return->setCustomerName($customer->getName());

                        $return->setRmaContact($customer->getEccContactCode());

                        $shipTo = $customer->getDefaultShippingAddress();
                        /* @var $shipTo Mage_Customer_Model_Address */

                        if ($shipTo) {
                            $return->setAddressCode($shipTo->getEccErpAddressCode());
                        }
                        $return->setCustomerId($customer->getId());
                        $return->setEmailAddress($customer->getEmail());
                    }

                    $return->setStoreId($this->storeManager->getStore()->getId());

                    $return->setActions('All');
                    //M1 > M2 Translation Begin (Rule 25)
                    //$return->setRmaDate(now());
                    $return->setRmaDate(date('Y-m-d H:i:s'));
                    //M1 > M2 Translation End

                    if (empty($errors)) {
                        $return->setCustomerReference($cusRef);
                        $return->save();
                    }

                    $this->registry->register('return_model', $return);
                    $this->registry->register('return_id', $return->getId());
                }
            }

            $this->sendStepResponse('return', $errors);
        }
    }

    }
