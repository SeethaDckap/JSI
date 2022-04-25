<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message;


/**
 * Base class for supplier connect messages
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 *
 * @method setMaxResults()
 * @method getMaxResults()
 * @method setRangeMin()
 * @method getRangeMin()
 */
class RequestsearchArray extends \Epicor\Supplierconnect\Model\Message\Request
{

    public function buildRequest()
    {
        //   don't proceed if account number not valid 
        if ($this->getAccountNumber()) {
            // add each line of message request 
            $maxResults = $this->getMaxResults() ?: $this->getConfig('max_results_value');
            $rangeMin = $this->getRangeMin() ?: $this->getConfig('range_min_value');

            $this->mergeSearches();

            $results = array(
                'maxResults' => $maxResults,
                'rangeMin' => $rangeMin,
                'searches' => $this->_mergedSearches
            );

            $this->addDisplayOption('results', $results);
            $this->addDisplayOption('accountNumber', $this->getAccountNumber());

            $data = $this->getMessageTemplate();
            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $this->_requestData);

            $this->setOutXml($data);

            return true;
        } else {
            return 'Missing account number';
        }
    }
    public function processResponseArray()
    {
        if ($this->getIsSuccessful()) {
            // getVarienDataFromPath converts xml into a varien object, which can be referenced from controller
            $result = $this->getResponse();
            $paths = $this->getResultsPath();
            $paths = explode("/",$paths);
            $finalData = &$result;
            foreach($paths as $key) {
                $finalData = &$finalData[$key];
            }
            $this->setResults($finalData);
            return true;
        } else {
            return false;
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