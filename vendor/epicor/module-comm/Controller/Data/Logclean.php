<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Data;

class Logclean extends \Epicor\Comm\Controller\Data
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
     * @var \Epicor\Comm\Model\Message\LogFactory
     */
    protected $commMessageLogFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Model\Message\LogFactory $commMessageLogFactory
    ) {
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->generic = $generic;
        $this->commMessageLogFactory = $commMessageLogFactory;
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $log = $this->commMessageLogFactory->create();
        $log->clean();
    }

    }
