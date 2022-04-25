<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Customer\Helper\View as CustomerViewHelper;

class MassResetPassword extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Customer
{


    /**
     *  Reset Password custom template for mass update
     */
    const RESET_PASSWORD_EMAIL_TEMPLATE_ID = 'epicor_comm_customer_password_remind_email_template';

    /**
     * System configuration path of Rest Password Email template
     */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'customer/password/reset_password_template';

    protected $_mailSendFailure = false;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $random;
    /*
     * @var \Magento\Customer\Model\CustomerRegistry $customerRegistry,
     */
    protected $customerRegistry;
    /*
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     *
     * @var array()
     */
    protected $mail_exception_message = array();

    /**
     * @var null
     */
    protected $exception_instance = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     *
     * /**
     *
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Framework\Math\Random $random
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Epicor\Common\Helper\Data $commonHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param DataObjectProcessor $dataProcessor
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Math\Random $random,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DataObjectProcessor $dataProcessor,
        CustomerViewHelper $customerViewHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        parent::__construct($context, $backendAuthSession);
        $this->backendSession = $context->getSession();
        $this->random = $random;
        $this->_customerRegistry = $customerRegistry;
        $this->_CustomerRepositoryInterface = $customerRepository;
        $this->encryptor = $encryptor;
        $this->commonHelper = $commonHelper;
        $this->scopeConfig = $scopeConfig;
        $this->dataProcessor = $dataProcessor;
        $this->customerViewHelper = $customerViewHelper;
        $this->registry = $commonHelper->getRegistry();
        $this->customerFactory = $customerFactory;
    }

    public function execute()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
            $this->messageManager->addError(__('Please select customer(s).'));
        } else {
            try {
                $newPassword = $this->getRequest()->getParam('password');
                $isEmailSend = $this->getRequest()->getParam('is_email_send') ? $this->getRequest()->getParam('is_email_send') : false;

                if (empty($newPassword)) {
                    $newPassword = $this->random->getRandomString(8);
                }

                if (strlen(trim($newPassword)) < 6) {
                    $this->messageManager->addError(__('The password must have at least 6 characters. Leading or trailing spaces will be ignored.'));
                    $this->_redirect('customer/index/index');
                    return;
                }
                $this->resetPassword($customersIds, $newPassword, $isEmailSend);

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were updated.', count($customersIds))
                );

            } catch (\Exception $e) {
                $this->backendSession->addError($e->getMessage());
            }
        }
        $this->_redirect('customer/index/index');
        return;
    }

    /**
     * reset password
     *
     * @param array $customersIds
     * @param string|null $newPassword
     * @param boolean $isEmailSend
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    private function resetPassword($customersIds, $newPassword, $isEmailSend)
    {
        foreach ($customersIds as $customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken(null);
            $customerSecure->setRpTokenCreatedAt(null);
            $customerSecure->setPasswordHash($this->encryptor->getHash($newPassword, true));

            try {
                //Save Password
                $connetion = $customer->getResource()->getConnection();
                $passwordHash = $customerSecure->getPasswordHash();
                $customer = $this->populateCustomerWithSecureData($customer, $passwordHash);
                $updateData = [
                    'password_hash' => $customer->getPasswordHash(),
                    'rp_token' => $customer->getRpToken(),
                    'rp_token_created_at' => $customer->getRpTokenCreatedAt()
                ];
                $connetion->update(
                    $connetion->getTableName('customer_entity'),
                    $updateData,
                    ['entity_id = ?' => (int)$customerId]
                );
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
                $this->_redirect('customer/index/index');
                return;
            }

            //Send Email
            if ($isEmailSend) {
                $customer = $this->_CustomerRepositoryInterface->getById($customerId);
                $this->sendEmail($customer, $customerSecure, $newPassword);
            }
        }

        if (count($this->mail_exception_message) > 0) {
            $this->messageManager->addException($this->exception_instance, (isset($this->mail_exception_message[0]) ? (string)$this->mail_exception_message[0] : ''));
        } else {
            if ($this->_mailSendFailure) {
                $this->messageManager->addErrorMessage(__('Unable to send email.'));
            }
        }

    }

    /**
     * Set secure data to customer model
     *
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param string|null $passwordHash
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    private function populateCustomerWithSecureData($customerModel, $passwordHash = null)
    {
        if ($customerModel->getId()) {
            $customerSecure = $this->_customerRegistry->retrieveSecureData($customerModel->getId());
            $customerModel->setRpToken($passwordHash ? null : $customerSecure->getRpToken());
            $customerModel->setRpTokenCreatedAt($passwordHash ? null : $customerSecure->getRpTokenCreatedAt());
            $customerModel->setPasswordHash($passwordHash ?: $customerSecure->getPasswordHash());

        }
        return $customerModel;
    }

    /**
     * Send Email to customer
     *
     * @param type $customer
     * @param type $customerSecure
     */
    private function sendEmail($customer, $customerSecure, $newPassword)
    {
        try {
            $customerData = $this->dataProcessor
                ->buildOutputDataArray($customer, '\Magento\Customer\Api\Data\CustomerInterface');

            $customerSecure->addData($customerData);
            $customerSecure->setData('name', $this->customerViewHelper->getCustomerName($customer));
            $customerSecure->setData('password', $newPassword);

            $from = $this->scopeConfig->getValue('customer/password/forgot_email_identity', ScopeInterface::SCOPE_STORES);
            $to = $customer->getEmail();
            $templateParams = ['customer' => $customerSecure];
            $template = $this->scopeConfig->getValue(self::XML_PATH_RESET_PASSWORD_TEMPLATE, ScopeInterface::SCOPE_STORES);
            if ($template == 'customer_password_reset_password_template') {
                $template = self::RESET_PASSWORD_EMAIL_TEMPLATE_ID;
            }
            $sendMailSuccess = $this->commonHelper->sendTransactionalEmailWithResponse($template, $from, $to, "", $templateParams);

            if (!$sendMailSuccess) {
                $this->_mailSendFailure = true;
            }
        } catch (\Exception $exception) {
            $this->exception_instance = $exception;
            $this->mail_exception_message[] = $exception->getMessage();
        }
    }
}
