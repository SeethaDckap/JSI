<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Source;

use Epicor\Comm\Model\Message\Log;

/**
 * Class Message Types
 */
class Messagetypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Epicor\Comm\Model\Message\Log
     */
    protected $commMessagingHelper;

    /**
     * @param CollectionFactory $log
     */
    public function __construct(
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        
        $this->commMessagingHelper = $commMessagingHelper;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {   
            $options[] = [];
            foreach ($this->commMessagingHelper->getMessageTypes() as $key=>$value) {
                $label = $this->commMessagingHelper->getMessageType($key);
                $options[] = ['label' => __($label), 'value' => $key];
            }
        return $options;
    }
}
