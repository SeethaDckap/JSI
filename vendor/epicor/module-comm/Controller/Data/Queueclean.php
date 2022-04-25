<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Data;

class Queueclean extends \Epicor\Comm\Controller\Data
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Comm\Model\Message\QueueFactory
     */
    protected $commMessageQueueFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Model\Message\QueueFactory $commMessageQueueFactory
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commMessageQueueFactory = $commMessageQueueFactory;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $queue = $this->commMessageQueueFactory->create();
        /* @var $queue Epicor_Comm_Model_Message_Queue */
        $queue->clean();
    }

    }
