<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Salesrepadd extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;
    /*
     * $var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected  $customerRepository;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Epicor\SalesRep\Controller\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository )
    {
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $salesRepAccount = $helper->getManagedSalesRepAccount();

        $data = $this->getRequest()->getParams();
        if ($data) {

            try {
                $customer = $this->customerCustomerFactory->create();
                /* @var $customer \Epicor\Comm\Model\Customer */
                $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
                $customer->loadByEmail($data['email_address']);

                $error = '';
                $msg = '';

                if (!$customer->isObjectNew()) {
                    $currentId = $customer->getEccSalesRepAccountId();
                    if (!empty($currentId) && $salesRepAccount->getId() != $currentId) {
                        $error = __('Existing Sales Rep Email Address Found. Cannot assign as a Sales Rep');
                    } else {
//                        $customer->setEccSalesRepId($data['sales_rep_id']);
//                        $customer->setEccSalesRepAccountId($salesRepAccount->getId());
//                        $customer->save();
//                        $msg = __('Existing Non-Sales Rep Customer Found. They have been updated to be a Sales Rep for %s', $salesRepAccount->getName());
                        $msg = __('Email assigned to existing customer / supplier. Cannot assign as a Sales Rep');
                    }
                } else {
                    $store = $this->storeManager->getWebsite()->getDefaultStore();
                    $customer->setStore($store);
                    $customer->setFirstname($data['first_name']);
                    $customer->setLastname($data['last_name']);
                    $customer->setEmail($data['email_address']);
                    $customer->setEccSalesRepId($data['sales_rep_id']);
                    $customer->setEccSalesRepAccountId($salesRepAccount->getId());
                    $customer->setPassword($this->mathRandom->getRandomString(10));
                    $customer->save();
                    
                   $customer_id =  $customer->getId();
                    if($customer_id){
                        $customerRepObject = $this->customerRepository->getById($customer_id);
                        $customerRepObject->setCustomAttribute('ecc_sales_rep_id',$data['sales_rep_id']);
                        $customerRepObject->setCustomAttribute('ecc_sales_rep_account_id',$salesRepAccount->getId());
                        $customerRepObject->setCustomAttribute('ecc_erp_account_type','salesrep');
                        $this->customerRepository->save($customerRepObject);
                    }
                    $customer->sendNewAccountEmail();

                    //M1 > M2 Translation Begin (Rule 55)
                    //$msg = __('New Sales Rep Created. An email has been sent to %s with login details', $data['email_address']);
                    $msg = __('New Sales Rep Created. An email has been sent to %1 with login details', $data['email_address']);
                    //M1 > M2 Translation End
                }
            } catch (\Exception $ex) {
                $this->logger->critical($ex);
                $error = __('An error occured, please try again'.$ex->getMessage());
            }

            if (!empty($error)) {
                $this->messageManager->addErrorMessage($error);
            } else {
                $this->messageManager->addSuccessMessage($msg);
            }
        }

        $this->_redirect('*/*/salesreps');
    }

}
