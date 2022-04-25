<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class GuestLogin extends \Epicor\Comm\Controller\Returns
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

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
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->customerCustomerFactory = $customerCustomerFactory;
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
        if ($this->getRequest()->isPost()) {
            $errors = array();
            $shiptoName = $this->getRequest()->getParam('shipto_name', false);
            $emailAddress = $this->getRequest()->getParam('email_address', false);

            if ($shiptoName === false) {
                $errors[] = __('Ship To Name Empty');
            }

            if ($emailAddress === false) {
                $errors[] = __('Email Address Empty');
            }

            if ($shiptoName !== false && $emailAddress !== false) {
                $session = $this->customerSession;
                /* @var $session Mage_Customer_Model_Session */

                $customer = $this->customerCustomerFactory->create();
                /* @var $customer Epicor_Comm_Model_Customer */

                $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
                $customer->loadByEmail($emailAddress);

                if ($customer->getId()) {
                    $errors[] = __('You must log in to proceed');
                } else {
                    $session->setReturnGuestName($shiptoName);
                    $session->setReturnGuestEmail($emailAddress);

                    $this->registry->register('guest_name', $shiptoName);
                    $this->registry->register('guest_email', $emailAddress);
                }
            }

            $this->sendStepResponse('login', $errors);
        }
    }

    }
