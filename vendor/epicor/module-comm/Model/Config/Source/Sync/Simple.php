<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Sync;


class Simple
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

        $messages = array();
        $simpleMessages = $this->commMessagingHelper->getSimpleMessageTypes('sync');
        if (!empty($simpleMessages)) {
            foreach ($simpleMessages as $type => $desc) {
                $desc = (array) $desc;
                $msgTypes = implode(',', $desc['value']);    // put codes required for task in csv string
                $messages[] = array(
                    'label' => $desc['label'],
                    'value' => $desc['label'],
                );
            }
        }

        return $messages;
    }

}
