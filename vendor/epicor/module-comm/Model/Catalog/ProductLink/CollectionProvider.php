<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Catalog\ProductLink;

/**
 * Provides info about product categories.
 *
 */
class CollectionProvider extends \Magento\Catalog\Model\ProductLink\CollectionProvider
{
    /**
     * Get product collection by link type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $type
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCollection(\Magento\Catalog\Model\Product $product, $type)
    {
        if (!isset($this->providers[$type])) {
            throw new NoSuchEntityException(__("The collection provider isn't registered."));
        }
        $products = $this->providers[$type]->getLinkedProducts($product);
        $linkData = $this->prepareList($products, $type);
        usort($linkData, function ($itemA, $itemB) {
            $posA = intval($itemA['position']);
            $posB = intval($itemB['position']);
            return $posA <=> $posB;
        });
        return $linkData;
    }

    /**
     * Fix compatible with M234 adn ECC .8.1
     * This fn should added from M2.3.4 core
     *
     * @param Product[] $linkedProducts
     * @param string $type
     * @return array
     */
    private function prepareList(array $linkedProducts, string $type): array
    {
        $converter = $this->converterPool->getConverter($type);
        $links = [];
        foreach ($linkedProducts as $item) {
            $itemId = $item->getId();
            $links[$itemId] = $converter->convert($item);
            $links[$itemId]['position'] = $links[$itemId]['position'] ?? 0;
            $links[$itemId]['link_type'] = $type;
        }

        return $links;
    }
}