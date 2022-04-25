<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request\Inventory;


/**
 * DealersConnect Request Search Message
 * 
 * @method setMaxResults()
 * @method getMaxResults()
 * @method setRangeMin()
 * @method getRangeMin()
 */
class Requestsearch extends \Epicor\Dealerconnect\Model\Message\Request\Inventory\Request
{

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {
            $maxResults = $this->getMaxResults() ?: $this->getConfig('max_results_value');
            $rangeMin = $this->getRangeMin() ?: $this->getConfig('range_min_value');
            //SFtest... to be removed when the address filter is implemented
            //  $this->addSearchOption('addressCode', 'IN', array('test1', 'test2', 'test3'));
            //...SFtest
            $this->mergeSearches();
            
            $this->mergeAttributeSearches();
            
            $results = array(
                'maxResults' => $maxResults,
                'rangeMin' => $rangeMin,
                'searches' => $this->_mergedSearches,
                'attributes' => $this->_mergedAttributeSearches
            );
            $this->addDisplayOption('results', $results);

            $this->addSecondaryAccountNumbers();                                        // add child account numbers
            if ($this->_accountNumbers) {
                $this->addDisplayOption('accountNumber', '');         // current user account number
                $this->addDisplayOption('accounts', $this->_accountNumbers);
            } else {
                $postdata = $this->request->getPostValue();
                $claimNum = isset($postdata['claim_number']) ? $postdata['claim_number'] : '';

                if($this->getMessageType() === 'DEIS'){
                    if(!$claimNum){
                        $DeisFilterType = $this->customerSession->getDeisFilterType();
                        if(!$DeisFilterType && $this->dealerHelper->checkCusInventorySearch() === 0){
                            $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // current user account number
                        }elseif($DeisFilterType === 'all'){
                            $this->addDisplayOption('accountNumber', '');         // current user account number
                        }elseif($DeisFilterType === 'dealergroup'){
                            $grpId = $this->dealerHelper->getDealerGroup();
                            $this->mapDealerGroups($grpId);
                            if(!empty($this->_grpNumbers)){
                                $this->addDisplayOption('accounts', $this->_grpNumbers);
                            }else{
                                $this->addDisplayOption('accountNumber', $this->getAccountNumber());
                            }
                        }else{
                            $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // current user account number
                        }
                    }else{
                        $DeisFilterType = $this->dealerHelper->checkCusClaimInventorySearch();
                        if(!$DeisFilterType || $DeisFilterType == 0){
                            $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // current user account number
                        }elseif($DeisFilterType == 1){
                            $this->addDisplayOption('accountNumber', '');         // current user account number
                        }elseif($DeisFilterType == 2){
                            $grpId = $this->dealerHelper->getClaimDealerGroup();
                            $this->mapDealerGroups($grpId, true);
                            $this->addDisplayOption('accounts', $this->_grpNumbers);
                        }else{
                            $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // current user account number
                        }
                    }

                }else{
                    $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // current user account number
                }
            }

            $this->addDisplayOption('languageCode', $this->getLanguageCode());

            if ($this->getIsCurrency()) {                                                 // currency code
                $currencies = array();

                if (count($this->_currencies) > 0)
                    $currencies['currency'] = $this->_currencies;

                $this->addDisplayOption('currencies', $currencies);
            }

            $data = $this->getMessageTemplate();
            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $this->_displayData);

            $this->setOutXml($data);
            return true;
        } else {
            return 'Missing account number';
        }
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            // getVarienDataFromPath converts xml into a varien object, which can be referenced from controller
            $this->setResults($this->getResponse()->getVarienDataArrayFromPath($this->getResultsPath()));
            return true;
        } else {
            return false;
        }
    }

    public function mapDealerGroups($grpId)
    {
        if ($grpId) {
            $dealerGrp = $this->dealerGroupModelFactory->create()->load($grpId);
            $exclusion = $dealerGrp->getDealerAccountsExclusion();
            $erpAccounts = $dealerGrp->getErpAccounts($dealerGrp->getId());
            if($exclusion == 'Y'){
                $erpAccountIds = implode(",", array_keys($erpAccounts));
                $erpAccounts = $dealerGrp->getValidErpAccounts($dealerGrp->getId(), $erpAccountIds, $exclusion);
            }
            foreach ($erpAccounts as $erpAccount) {
                $this->_grpNumbers['accountNumber'][] = $erpAccount->getAccountNumber();
            }
        }
    }
}