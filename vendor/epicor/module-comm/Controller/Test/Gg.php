<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class Gg extends \Epicor\Comm\Controller\Test
{



    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Magento\Framework\App\CacheInterface $cacheManager)
    {
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        parent::__construct(
            $context,
            $resourceConfig,
            $moduleReader,
            $cacheManager);
    }

public function execute()
    {
        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
//        
        $collection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_List_Collection */

        $collection->filterActive();

        $filters = $helper->getListFilters();
        foreach ($filters as $filter) {
            $filter->filter($collection);
        }

        $collection->getSelect()->group('main_table.id');

        echo (string) $collection->getSelect();

        echo '<br>';
        var_dump(count($helper->getActiveLists()));
        foreach ($helper->getActiveLists() as $list) {
            echo '<br>' . $list->getTitle();
        }
    }

    }
