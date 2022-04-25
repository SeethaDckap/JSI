<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Observer;

use Magento\Customer\Model\Session;

class UpdateCuau implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;


    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Session $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau
     * @param \Epicor\Customerconnect\Helper\Data $customerconnectHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Common\Helper\Xml $commonXmlHelper
    )
    {
        $this->_request = $request;
        $this->session = $customerSession;
        $this->customer = $customer;
        $this->_registry = $registry;
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_localeResolver = $localeResolver;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        $this->commonXmlHelper = $commonXmlHelper;
    }

    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $old_form_data = $this->_registry->registry('oldContact');
        $form_data = $this->_registry->registry('newContact');

        //temporarily remove the login_id from the arrays to be compared
        // as cuau is not to be sent if this changes
        $oldDataNewvalue = $old_form_data;
        $newDataNewvalue = $form_data;
        unset($oldDataNewvalue['login_id']);
        unset($newDataNewvalue['login_id']);

        if (!isset($form_data['ecc_erpaccount_changed']) && $oldDataNewvalue != $newDataNewvalue) {

            $email = $observer->getEvent()->getEmail();
            if (!$email && $customer = $observer->getEvent()->getCustomer()) {
                $email = $customer->getEmail();
            }
            $customer = $this->customerCustomerFactory->create();
            $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
            $customer->loadByEmail($email);

            $message = $this->customerconnectMessageRequestCuau;
            $message->addContact('U', $form_data, $old_form_data);
            $this->sendUpdate($message, $customer);
        }
        $this->_registry->unregister('oldContact');
        $this->_registry->unregister('newContact');
    }

    /**
     * Index action
     *
     * @var $message \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected function sendUpdate($message, $customer)
    {
        $helper = $this->customerconnectHelper;
        $erpAccountId = $customer->getEccErpaccountId();
        $erp_account_number = $helper->getErpAccountNumber($erpAccountId);
        $messageTypeCheck = $message->getHelper()->getMessageType('CUAU');
        $error = false;
        if ($message->isActive() && $messageTypeCheck && $erpAccountId) {
            $message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            $customer->setEccCucoPending('1');
            $customer->getResource()->saveAttribute($customer, 'ecc_cuco_pending');

            if ($message->sendMessage()) {
                $response = $message->getResponse();
                $xmlHelper = $this->commonXmlHelper;
                $contacts = $xmlHelper->varienToArray($response->getCustomer()->getContacts());
                $currentEmail = $customer->getEmail();                
                if(isset($contacts['contact'][0])){
                    $contactsArray  = $contacts['contact'];
                }else{
                    $contactsArray []= $contacts['contact'];
                }
                $filteredContact = array_values(array_filter($contactsArray, function($arrayValue) use($currentEmail) {
                    return $arrayValue['email_address'] == $currentEmail;
                }));
                if (isset($filteredContact[0]['login_id']) && $filteredContact[0]['login_id']) {
                    $customer->setEccErpLoginId($filteredContact[0]['login_id']);
                    $customer->getResource()->saveAttribute($customer, 'ecc_erp_login_id');
                }
                $customer->setEccCucoPending('0');
                $customer->getResource()->saveAttribute($customer, 'ecc_cuco_pending');
            }
        }
    }
}
