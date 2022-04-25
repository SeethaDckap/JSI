<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Gj extends \Epicor\Comm\Controller\Test
{



    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Magento\Framework\App\CacheInterface $cacheManager)
    {
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        parent::__construct(
            $context,
            $resourceConfig,
            $moduleReader,
            $cacheManager);
    }

    public function execute()
    {
        echo '<pre>';
        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */

        var_dump($helper->getActiveListsProductIds());
        var_dump($helper->getContractsForProduct(6));
    }

    }
