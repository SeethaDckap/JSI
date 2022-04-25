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
class Messagestatus
{

    /**
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    public function __construct(
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory
    ) {
        $this->commMessageLogFactory = $commMessageLogFactory;
    }
    public function toOptionArray()
    {
        $options = array();
        $log = $this->commMessageLogFactory->create();
        $col = $log->getMessageStatuses();
        foreach ($col as $key => $value) {
            $options[] = array('value' => $key, 'label' => ucwords($value));
        }

        return $options;
    }

}
