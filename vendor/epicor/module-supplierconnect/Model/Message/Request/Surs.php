<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SUSD - Supplier Upload Summary Details  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported 
 * /results/maxResults                                      - supported
 * /results/rangeMin                                        - supported
 * /results/searches/search/criteria                        - supported
 * /results/searches/search/condition                       - supported
 * /results/searches/search/value                           - supported
 * /accountNumber                                           - supported 
 * /languageCode                                            - supported  

 * 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Surs extends \Epicor\Supplierconnect\Model\Message\RequestsearchArray
{

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Supplierconnect\Helper\Messaging $supplierconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->supplierconnectHelper = $supplierconnectHelper;
        parent::__construct(
            $context,
            $supplierconnectMessagingHelper,
            $localeResolver,
            $resource,
            $resourceCollection,
            $data
        );
        $this->setMessageType('SURS');
        $this->setConfigBase('supplierconnect_enabled_messages/SURS_request/');
        $this->setResultsPath('rfqs/rfq');
        $helper =  $supplierconnectHelper;
    }

}
