<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
class CommsCustomerCustomerAuthenticated extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Data */
        $customer = $observer->getEvent()->getModel();
        /* @var $customer \Epicor\Comm\Model\Customer */
        if ($customer->isForcedToMasqurade()) {
            $helper->wipeCart();
        }

        if ((!$customer->isSupplier() && !$customer->isValidForStoreLogin()) ||
            ($customer->isSupplier() && !$customer->isValidForStoreLogin(null, 'supplier'))) {
            //M1 > M2 Translation Begin (Rule P2-9)
            //throw Mage::exception('Mage_Core', $this->__('Invalid login or password (001)'), \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password (001)'));
            //M1 > M2 Translation End

        }

        $restriction = $this->scopeConfig->getValue('customer/onstop/restriction', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($restriction == 'login') {
            if (!$customer->isSupplier()) {
                $erpAccount = $helper->getErpAccountInfo();
                /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
                if ($erpAccount && !$erpAccount->isObjectNew() && $erpAccount->getOnstop($this->storeManager->getStore()->getBaseCurrencyCode())) {
                    //M1 > M2 Translation Begin (Rule P2-9)
                    //throw Mage::exception('Mage_Core', $this->__('Invalid login or password (002)'), \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
                    throw new InvalidEmailOrPasswordException(__('Invalid login or password (002)'));
                    //M1 > M2 Translation End

                }
            }
        }

        $helper = $this->commonHelper->create();

        if ($customer->isCustomer() && !$helper->isLicensedFor(array('Customer'))) {
            //M1 > M2 Translation Begin (Rule P2-9)
            //throw Mage::exception('Mage_Core', $this->__('Invalid login or password (003)'), \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password (003)'));
            //M1 > M2 Translation End


        } else if ($customer->isSupplier() && !$helper->isLicensedFor(array('Supplier'))) {
            //M1 > M2 Translation Begin (Rule P2-9)
            //throw Mage::exception('Mage_Core', $this->__('Invalid login or password (004)'), \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password (004)'));
            //M1 > M2 Translation End
        } else if ($customer->isGuest(false) && !$helper->isLicensedFor(array('Consumer'))) {
            //M1 > M2 Translation Begin (Rule P2-9)
            //throw Mage::exception('Mage_Core', $this->__('Invalid login or password (005)'), \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password (005)'));
            //M1 > M2 Translation End
        } else if (($customer->isDealer(false) || $customer->isDistributor(false)) && !$helper->isLicensedFor(array('Dealer_Portal'))) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password (006)'));
        }

        $ast = $this->commMessageRequestAstFactory->create();

        //Multi Erp Account
        $getErpAcctCounts = $customer->getErpAcctCounts();
        $sendAst=true;
        $favErpId=false;
        if(is_array($getErpAcctCounts) && count($getErpAcctCounts) > 1){
            $favErpId= $customer->getFavErpId();
            $sendAst=false;
        }
        /* @var $ast \Epicor\Comm\Model\Message\Request\Ast */
        if (!$this->registry->registry('SkipEvent') && $ast->isActive('ast_at_login')) {
            if (!$customer->isSupplier() && $sendAst) {
                // don't send ast message on login if supplier
                $this->getAccountDetails($observer);
            }
        }

        // make sure masquerading is not happening
        $customerSession = $this->customerSessionFactory->create();
        $customerSession->setMasqueradeAccountId(false);
        $customerSession->setDisplayLocations(false);
        if($favErpId){
            $this->commHelper->create()->startMasquerade($favErpId);
        }

        return $this;
    }

    public function getAccountDetails($observer)
    {
        $customer = $observer->getEvent()->getModel();
        $ast = $this->commMessageRequestAstFactory->create();
//        $ast = Mage::getModel('epicor_comm/message_request_ast');
        /* @var $ast \Epicor\Comm\Model\Message\Request\Ast */
        $ast->setCustomer($customer);
        $ast->sendMessage();
        return $this;
    }

}