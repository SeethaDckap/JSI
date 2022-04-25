<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message;


/**
 * Customerconnect Request Search Message
 * 
 * @method setMaxResults()
 * @method getMaxResults()
 * @method setRangeMin()
 * @method getRangeMin()
 */
class Requestsearch extends \Epicor\Customerconnect\Model\Message\Request
{

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {
            $maxResults = $this->getMaxResults() ?: $this->getConfig('max_results_value');
            $rangeMin = $this->getRangeMin() ?: $this->getConfig('range_min_value');
            $this->mergeSearches();
            $results = array(
                'maxResults' => $maxResults,
                'rangeMin' => $rangeMin,
                'searches' => $this->_mergedSearches
            );
            $this->addDisplayOption('results', $results);

            $this->addSecondaryAccountNumbers();                                        // add child account numbers
            if ($this->_accountNumbers) {
                $this->addDisplayOption('accountNumber', '');         // current user account number
                $this->addDisplayOption('accounts', $this->_accountNumbers);
            } else {
                $this->addDisplayOption('accountNumber', $this->getAccountNumber());         // current user account number
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

}
