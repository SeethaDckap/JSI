<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Magento\Customer\Model\Session;
use Epicor\Punchout\Api\ConnectionsRepositoryInterface;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\Punchout\Helper\Data;

/**
 * Shipping Model
 */
class Shipping
{

    /**
     * Customer session.
     *
     * @var Session
     */
    private $customerSession;

    /**
     * Connection repository interface.
     *
     * @var ConnectionsRepositoryInterface
     */
    private $connectionRepository;

    /**
     * Json Serializer
     *
     * @var Serializer
     */
    private $serializer;


    /**
     * Shipping constructor.
     *
     * @param Session                        $customerSession      Customer Session.
     * @param ConnectionsRepositoryInterface $connectionRepository Customer repository.
     * @param Data                           $helper               Helper class.
     */
    public function __construct(
        Session $customerSession,
        ConnectionsRepositoryInterface $connectionRepository,
        Data $helper
    ) {
        $this->customerSession      = $customerSession;
        $this->connectionRepository = $connectionRepository;
        $this->serializer           = $helper->getSerializer();

    }//end __construct()


    /**
     * Get ERP shipping method mapping.
     *
     * @param string  $shippingCode Procurement shipping code.
     * @param integer $connectionId Connection ID.
     *
     * @return string
     */
    public function getErpMapping($shippingCode, $connectionId)
    {
        $connection   = $this->connectionRepository->loadEntity($connectionId);
        if ($connection->getId()) {
            $shippingMapping = $this->serializer->unserialize($connection->getShippingMappings());
            $map             = array_filter(
                $shippingMapping,
                function ($arrayValue) use ($shippingCode) {
                    return strtoupper($arrayValue['code']) == strtoupper($shippingCode);
                }
            );

            $map = array_pop($map);
            if (!empty($map) && isset($map['erp_code'])) {
                return $map['erp_code'];
            }
        }

        return $shippingCode;

    }//end getErpMapping()


}//end class
