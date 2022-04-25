<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Config\Source;


/**
 * Quote Status dropwdown
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Quotestatus
{

    const QUOTE_STATUS_PENDING = 'pending';
    const QUOTE_STATUS_AWAITING = 'awaiting_acceptance';
    const QUOTE_STATUS_EXPIRED = 'expired';
    const QUOTE_STATUS_REJECTED_CUSTOMER = 'rejected_customer';
    const QUOTE_STATUS_REJECTED_ADMIN = 'rejected_admin';
    const QUOTE_STATUS_ACCEPTED = 'accepted';
    const QUOTE_STATUS_ORDERED = 'ordered';

    private $_descriptions = array(
        self::QUOTE_STATUS_PENDING => 'Pending',
        self::QUOTE_STATUS_AWAITING => 'Awaiting Acceptance',
        self::QUOTE_STATUS_EXPIRED => 'Expired',
        self::QUOTE_STATUS_REJECTED_CUSTOMER => 'Rejected by Customer',
        self::QUOTE_STATUS_REJECTED_ADMIN => 'Rejected',
        self::QUOTE_STATUS_ACCEPTED => 'Accepted',
        self::QUOTE_STATUS_ORDERED => 'Ordered',
    );

    public function toOptionArray()
    {
        return array(
            array('value' => 'pending', 'label' => "Pending"),
            array('value' => 'awaiting_acceptance', 'label' => 'Awaiting Acceptance'),
            array('value' => 'expired', 'label' => 'Expired'),
            array('value' => 'rejected_customer', 'label' => 'Rejected by Customer'),
            array('value' => 'rejected_admin', 'label' => 'Rejected'),
            array('value' => 'accepted', 'label' => 'Accepted'),
            array('value' => 'ordered', 'label' => 'Ordered'),
        );
    }

    public function getStatusDescription($state)
    {
        return $this->_descriptions[$state];
    }

}
