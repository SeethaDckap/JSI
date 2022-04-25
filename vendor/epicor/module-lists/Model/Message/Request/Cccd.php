<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Message\Request;


/**
 * Request CCCD - Contract details  
 * 


 * 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cccd extends \Epicor\Customerconnect\Model\Message\Requestsearch
{

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $customerconnectMessagingHelper, $localeResolver, $resource, $resourceCollection, $data);
        $this->setMessageType('CCCD');
        $this->setConfigBase('customerconnect_enabled_messages/CCCD_request/');
        $this->setResultsPath('contracts/contract');
        $this->setIsCurrency(true);
    }


    /**
     * builds the CCCD message
     * 
     * @return boolean
     */
    public function buildRequest()
    {
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['contractCode'] = $this->getContractCode();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();

        $this->setOutXml($data);

        return true;
    }

}
