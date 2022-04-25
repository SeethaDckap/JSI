<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request DCLS - Dealer Claim Management Search 
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Dcls extends \Epicor\Comm\Model\Message\Requestsearch
{
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('DCLS');
            $this->setLicenseType('Dealer_Portal');
            $this->setConfigBase('dealerconnect_enabled_messages/DCLS_request/');
            $this->setResultsPath('claims/case');
    }
    
    public function mergeSearches()
    {
        if (array_key_exists('search', $this->_searchCriteria) && array_key_exists('search', $this->_searchInCriteria)) {
            $this->_mergedSearches['search'] = array_merge($this->_searchCriteria['search'], $this->_searchInCriteria['search']);
        } elseif (array_key_exists('search', $this->_searchCriteria)) {
            if (isset($this->_searchCriteria['search'][0]['criteria']) && $this->_searchCriteria['search'][0]['criteria'] == "serialNumbersSerialNumber") {
                $this->_searchCriteria['search'][0]['criteria'] = 'serialNumbers';
            }
            $this->_mergedSearches['search'] = $this->_searchCriteria['search'];
        } elseif (array_key_exists('search', $this->_searchInCriteria)) {
            $this->_mergedSearches['search'] = $this->_searchInCriteria['search'];
        }
    }

}
