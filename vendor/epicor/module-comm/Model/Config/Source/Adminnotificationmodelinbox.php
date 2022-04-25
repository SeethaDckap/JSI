<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminNotificationModelInbox
 *
 * @author David.Wylie
 */
class Adminnotificationmodelinbox
{

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $adminNotificationInboxFactory;

    public function __construct(
        \Magento\AdminNotification\Model\InboxFactory $adminNotificationInboxFactory
    ) {
        $this->adminNotificationInboxFactory = $adminNotificationInboxFactory;
    }
    public function toOptionArray()
    {
        $options = array();
        $a = $this->adminNotificationInboxFactory->create();
        $col = $a->getSeverities();
        foreach ($col as $key => $value) {
            $options[] = array('value' => $key, 'label' => ucwords($value));
        }

        return $options;
    }

}
