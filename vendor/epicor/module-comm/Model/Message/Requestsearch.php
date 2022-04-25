<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message;


/**
 * Comm Request Search Message
 * 
 * @method setMaxResults(integer $max)
 * @method setResults(array $results)
 * @method setRangeMin(integer $max)
 * @method integer getMaxResults()
 * @method array getResults()
 * @method integer getRangeMin()
 */
class Requestsearch extends \Epicor\Comm\Model\Message\Request
{

    protected $_currencies = array();

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {

            $maxResults = $this->getMaxResults() ?: $this->getConfig('max_results_value');
            $rangeMin = $this->getRangeMin() ?: $this->getConfig('range_min_value');

            $data = $this->getMessageTemplate();

            $this->mergeSearches();

            $results = array(
                'results' => array(
                    'maxResults' => $maxResults,
                    'rangeMin' => $rangeMin,
                    'searches' => $this->_mergedSearches
                ),
            );

            $this->addSecondaryAccountNumbers();
            if ($this->_accountNumbers) {
                $results['accountNumber'] = '';
                $results['accounts'] = $this->_accountNumbers;
            } else {
                $results['accountNumber'] = $this->getAccountNumber();
            }
            $results['languageCode'] = $this->getLanguageCode();
            if (count($this->_currencies) > 0) {
                $results['currencies'] = array(
                    'currency' => $this->_currencies
                );
            }

            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $results);

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

    public function addCurrencyOption($fieldName, $value)
    {
        $this->_currencies[] = array(
            $fieldName => $value
        );
        return $this;
    }

}
