<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Data;

class Synlogcleanup extends \Epicor\Comm\Controller\Data
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
     * @var \Epicor\Comm\Model\CronFactory
     */
    protected $commCronFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Model\CronFactory $commCronFactory
    ) {
        $this->commCronFactory = $commCronFactory;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        echo '<pre>';
        $cron = $this->commCronFactory->create();
        $cron->cleanupSynLog();
    }

    }
