<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request CCMS - Customer Case Management Search 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Ccms extends \Epicor\Comm\Model\Message\Requestsearch
{
public function __construct(
    \Epicor\Comm\Model\Context $context,
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
    array $data = [])
{
    parent::__construct($context, $resource, $resourceCollection, $data);
    $this->setMessageType('CCMS');
        $this->setLicenseType('Consumer');
        $this->setConfigBase('epicor_comm_enabled_messages/CCMS_request/');
        $this->setResultsPath('cases/case');
}

}
