<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Advanced
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
    }
    public function toOptionArray()
    {

//        $messageTypes = Mage::getStoreConfig("epicor_comm_enabled_messages/syn_request/autosync_simple_messages");
        $messages = array();
        $messageTypes = $this->commMessagingHelper->getMessageTypes('upload');
        if (!empty($messageTypes)) {
            foreach ($messageTypes as $type => $desc) {
                $desc = (array) $desc;
                $messages[] = array(
                    'label' => $desc['label'],
                    'value' => strtoupper($type),
                );
            }
        }

        return $messages;
    }

}
