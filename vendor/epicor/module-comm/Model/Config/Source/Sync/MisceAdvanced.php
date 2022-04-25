<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Config\Source\Sync;


use Magento\Framework\Setup\ModuleDataSetupInterface;

class MisceAdvanced
{


    const TYPE = 'misce';

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    private $commMessagingHelper;

    /**
     * @param Epicor\Comm\Helper\Messaging $commMessagingHelper
     */

    public function __construct(
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    )
    {
        $this->commMessagingHelper = $commMessagingHelper;
    }

    /**
     *  Return the Miscellaneous Messages
     *
     * @return array
     */

    public function toOptionArray()
    {
        $messages = array();
        $messageTypes = $this->commMessagingHelper->getMessageTypes('upload');
        $types = $this->commMessagingHelper->getAutoSyncType(true);
        $types = $types[self::TYPE];
        if (!empty($messageTypes)) {
            foreach ($messageTypes as $type => $desc) {
                $desc = (array)$desc;
                $type = strtoupper($type);
                if (in_array($type, $types)) {
                    $messages[] = array(
                        'label' => $desc['label'],
                        'value' => $type,
                    );
                }
            }
        }

        return $messages;
    }

}
