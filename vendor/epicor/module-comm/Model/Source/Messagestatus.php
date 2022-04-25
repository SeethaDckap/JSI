<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Source;

use Epicor\Comm\Model\Message\Log;

/**
 * Class Log
 */
class Messagestatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var Epicor\Comm\Model\Message\Log
     */
    protected $log;

    /**
     * @param CollectionFactory $log
     */
    public function __construct(
        \Epicor\Comm\Model\Message\Log $log
    ) {
        $this->log = $log;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {   
            $options[] = [];
            foreach ($this->log->getMessageStatuses() as $key=>$value) {
                $options[] = ['label' => __($value), 'value' => $key];
            }
        return $options;
    }
}
