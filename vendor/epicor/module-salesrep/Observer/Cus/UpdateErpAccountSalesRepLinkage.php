<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Cus;

class UpdateErpAccountSalesRepLinkage extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $cus = $observer->getEvent()->getMessage();
        /* @var $cus Epicor_Comm_Model_Message_Upload_Cus */
        if ($cus->isSuccessfulStatusCode()) {

            $erpAccount = $cus->getCusErpAccount();
            if ($erpAccount) {
                $salesRepId = $erpAccount->getSalesRep();
                if ($erpAccount->getOrigData('sales_rep') != $salesRepId) {
                    if ($erpAccount->getOrigData('sales_rep')) {
                        //remove old sales rep linkage
                        $oldSalesRep = $this->salesRepAccountFactory->create()->load($erpAccount->getOrigData('sales_rep'), 'sales_rep_id');
                        $oldExistingErpAccounts = $oldSalesRep->getErpAccountIds();
                        if (($key = array_search($erpAccount->getId(), $oldExistingErpAccounts)) !== false) {
                            unset($oldExistingErpAccounts[$key]);
                            $oldSalesRep->setErpAccounts($oldExistingErpAccounts);
                            $oldSalesRep->save();
                        }
                    }
                    if ($salesRepId) {
                        //check if sales rep exists
                        $salesRep = $this->salesRepAccountFactory->create()->load($salesRepId, 'sales_rep_id');
                        /* @var $salesRep Epicor_SalesRep_Model_Account */
                        if (!$salesRep->getId()) {
                            // create dummy sales rep
                            $salesRep->setIsDummy(true);
                            $salesRep->setSalesRepId($salesRepId);
                            $salesRep->setName($salesRepId);
                        }

                        $existingErpAccounts = $salesRep->getErpAccountIds();
                        if (!in_array($erpAccount->getId(), $existingErpAccounts)) {
                            $existingErpAccounts[] = $erpAccount->getId();
                            $salesRep->setErpAccounts($existingErpAccounts);
                        }

                        //save company from one or more branding values 
                        $brands = unserialize($erpAccount->getBrands());
                        $companyArray = array();
                        foreach ($brands as $brand) {
                            if (isset($brand['company']) && !in_array($brand['company'], $companyArray)) {
                                $companyArray[] = $brand['company'];
                            }
                        }

                        if (sizeof($companyArray)) {
                            $salesRep->setCompanies($companyArray);
                        }
                        $salesRep->save();
                    }
                }
            }
        }
    }

}