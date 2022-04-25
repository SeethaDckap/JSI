<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Controller\Password;

class Update extends \Epicor\Supplierconnect\Controller\Password
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Session\Generic $generic,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->generic = $generic;
        $this->logger = $logger;
    }
    public function execute()
    {
        $parms = $this->getRequest()->getParam('login');
        $customerSession = $this->customerSession;
        $newPassword = $parms['new_password'];
        if ($customerSession->isLoggedIn()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->customerCustomerFactory->create()->load($customerSession->getId());
            if (!empty($newPassword)) {
                try {
                    $customer->changePassword($newPassword);
                    $customer->sendPasswordReminderEmail();
                    $this->generic->addSuccess(__('Password changed successfully. Confirmation email sent.'), true);
                } catch (Mage_Core_Exception $e) {
                    $this->logger->debug('--- core exception when updating user password in supplierconnect ---');
                    $this->logger->debug($e);
                    $this->generic->addError(
                        __('A core exception error occurred while saving the customer. Password not changed'), true);
                } catch (\Exception $e) {
                    $this->logger->debug('--- exception when updating user password in supplierconnect ---');
                    $this->logger->debug($e);
                    $this->generic->addError(
                        __('An error occurred while saving the customer. Password not changed '), true);
                }
            }
        }

        $this->_redirect('*/account/index');            // return to supplierconnect dashboard
    }

}
