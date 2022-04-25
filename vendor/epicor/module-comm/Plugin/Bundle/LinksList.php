<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Bundle;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Model\Product;

class LinksList
{
    /**
     * @var LinkInterfaceFactory
     */
    private $linkFactory;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * LinksList constructor.
     * @param LinkInterfaceFactory $linkFactory
     * @param Type $type
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        LinkInterfaceFactory $linkFactory,
        Type $type,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->linkFactory = $linkFactory;
        $this->type = $type;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Removed the condition of $bundledProductPrice <= 0
     * @param \Magento\Bundle\Model\Product\LinksList $subject
     * @param array $result
     * @param ProductInterface $product
     * @param int $optionId
     * @return array
     */
    public function afterGetItems(
        \Magento\Bundle\Model\Product\LinksList $subject,
        $result,
        ProductInterface $product,
        $optionId
    ) {
        $selectionCollection = $this->type->getSelectionsCollection([$optionId], $product);

        $productLinks = [];
        /** @var Product $selection */
        foreach ($selectionCollection as $selection) {
            $bundledProductPrice = $selection->getSelectionPriceValue();
            $selectionPriceType = $product->getPriceType() ? $selection->getSelectionPriceType() : null;
            $selectionPrice = $bundledProductPrice ? $bundledProductPrice : null;

            /** @var LinkInterface $productLink */
            $productLink = $this->linkFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productLink,
                $selection->getData(),
                LinkInterface::class
            );
            $productLink->setIsDefault($selection->getIsDefault())
                ->setId($selection->getSelectionId())
                ->setQty($selection->getSelectionQty())
                ->setCanChangeQuantity($selection->getSelectionCanChangeQty())
                ->setPrice($selectionPrice)
                ->setPriceType($selectionPriceType);
            $productLinks[] = $productLink;
        }
        return $productLinks;
    }
}


