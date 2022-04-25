<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class AdminCustomerSaveAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

     public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory
    ) {
        $this->customerRepository = $context->getCustomerRepository();
        $this->customerCustomerFactory = $context->getCustomerFactory();
        $this->erpAccountFactory = $erpAccountFactory;
    }
    /**
     * Does any custom saving of a customer after save action in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        $customerModel = $this->customerCustomerFactory->create()->load($customer->getId());
        $customerRepository = $this->customerRepository->getById($customer->getId());
        $erpAcctCounts = $customerModel->getErpAcctCounts();

        $eccContactCode = "";
        if($customer->getCustomAttribute('ecc_contact_code')){
            $eccContactCode = $customer->getCustomAttribute('ecc_contact_code')->getValue();
        }
        if($customer->getCustomAttribute('ecc_erpaccount_id')){
            $erpAccountId = $customer->getCustomAttribute('ecc_erpaccount_id')->getValue();
        }
        $accountType = $customer->getCustomAttribute('ecc_erp_account_type')->getValue();
        if(empty($erpAcctCounts) || (!empty($erpAcctCounts) && count($erpAcctCounts) == 1)){
            if(!empty($erpAccountId) && $accountType === 'customer' && !empty($erpAcctCounts)){
                //update existing ERP Account > ERP Account
                $data = [
                    'erp_account_id' => $erpAccountId,
                    'customer_id' => $customer->getId(),
                    'erp_account_type' => $customer->getCustomAttribute('ecc_erp_account_type')->getValue(),
                    'contact_code' => $eccContactCode];
                $this->erpAccountFactory->create()->setData($data)->updateByCustomerId();
            }elseif(empty($erpAcctCounts) && $accountType === 'customer' && !empty($erpAccountId)){
                //update existing Guest/Salesrep/Supplier > ERP Account
                $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                $extensionAttributes->setEccMultiErpId($erpAccountId);
                $extensionAttributes->setEccMultiContactCode($eccContactCode);
                $extensionAttributes->setEccMultiErpType('customer');
                $customerRepository->setExtensionAttributes($extensionAttributes);
            }elseif (in_array($accountType, ['guest', 'salesrep', 'supplier'])){
                //update existing  ERP Account  > Guest/Salesrep/Supplier
                if(!empty($erpAcctCounts)){
                    $erpIdToDel = $erpAcctCounts[0]['erp_account_id'];
                    $customerModel->deleteErpAcctById($erpIdToDel);
                }
            }
        }

        if(empty($eccContactCode)){
            $customerModel->save();
            $customerRepository->setCustomAttribute('ecc_web_enabled', 1);
        }
        $this->customerRepository->save($customerRepository);
    }
}