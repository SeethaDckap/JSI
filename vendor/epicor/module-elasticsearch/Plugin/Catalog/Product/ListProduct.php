<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin\Catalog\Product;

use Epicor\Elasticsearch\Model\ResourceModel\Boost\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\App\Request\Http as Request;
use Epicor\Elasticsearch\Api\Data\BoostInterface;

/**
 * Plugin that handles full page caching
 *
 */
class ListProduct
{
    /**
     * @var CollectionFactory
     */
    private $boostCollectionFactory;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * Provider constructor.
     *
     * @param CollectionFactory $boostCollectionFactory
     * @param Request $request
     * @param StoreManager $storeManager
     */
    public function __construct(
        CollectionFactory $boostCollectionFactory,
        Request $request,
        StoreManager $storeManager
    ) {
        $this->boostCollectionFactory = $boostCollectionFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * Rewrite Identities for catalog search
     *
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param array $identities
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetIdentities(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        array $identities
    )
    {
        $checkIfCatalogSearchAction = $this->request->getFullActionName();
        if($checkIfCatalogSearchAction == "catalogsearch_result_index")
        {
            $collection = $this->boostCollectionFactory->create();
            $collection->addFieldToFilter(BoostInterface::STORE_ID,
                $this->storeManager->getStore()->getId());
            foreach ($collection->getItems() as $item) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $identities = array_merge($identities, $item->getIdentities());
            }
            return $identities;
        }
        else
        {
            return $identities;
        }
    }
}
