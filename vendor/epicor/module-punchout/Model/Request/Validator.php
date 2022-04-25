<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Request;

use Epicor\Punchout\Model\ResourceModel\Connections\CollectionFactory;

/**
 * Class Validator
 *
 * @package Epicor\Punchout\Model\Request
 */
class Validator
{

    /**
     * Connection collection.
     *
     * @var CollectionFactory
     */
    private $collectionFactory;


    /**
     * Constructor.
     *
     * @param CollectionFactory $collectionFactory Connection collection factory.
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;

    }//end __construct()


    /**
     * Get Active PunchoutConnection Data.
     *
     * @param string $identity     Customer ERP_CODE.
     * @param string $sharedSecret Secret key.
     *
     * @return array
     */
    public function getPunchoutConnection(string $identity, string $sharedSecret)
    {
        $connectionCollection = $this->collectionFactory->create();
        $connection           = $connectionCollection->
        addFieldToFilter('identity', $identity)
            ->addFieldToFilter('shared_secret', $sharedSecret)
            ->addFieldToFilter('is_active', '1')->getFirstItem();
        if (!empty($connection->getData())) {
            return $connection;
        }

    }//end getPunchoutConnection()


}//end class
