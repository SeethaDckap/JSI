<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Data;

class Scheduleimage extends \Epicor\Comm\Controller\Data
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
     * @var \Epicor\Comm\Model\Cron\ProductFactory
     */
    protected $commCronProductFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Model\Cron\ProductFactory $commCronProductFactory
    ) {
        $this->commCronProductFactory = $commCronProductFactory;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        parent::__construct(
            $context
        );
    }


/**
     * Schedule/tester action for the Asyncronous Image assignment
     */
    public function execute()
    {
        echo '<pre>';
        echo 'Create Cron';
        $cron = $this->commCronProductFactory->create();
        echo "\n Schedule Image";
        $cron->scheduleImage();
    }

    }
