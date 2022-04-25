<?php
/**
 * Import entity configurable product type model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Comm\Model\Import\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;

/**
 * Importing configurable products
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable
{

    /**
     * Array of SKU to array of super attribute values for all products.
     *
     * @param array $bunch - portion of products to process
     * @param array $newSku - imported variations list
     * @param array $oldSku - present variations list
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _loadSkuSuperAttributeValues($bunch, $newSku, $oldSku)
    {
        if ($this->_superAttributes) {
            $attrSetIdToName = $this->_entityModel->getAttrSetIdToName();

            $productIds = [];
            foreach ($bunch as $rowData) {
                $dataWithExtraVirtualRows = $this->_parseVariations($rowData);
                if (!empty($dataWithExtraVirtualRows)) {
                    array_unshift($dataWithExtraVirtualRows, $rowData);
                } else {
                    $dataWithExtraVirtualRows[] = $rowData;
                }

                foreach ($dataWithExtraVirtualRows as $data) {
                    if (!empty($data['_super_products_sku'])) {
                        if (isset($newSku[$data['_super_products_sku']])) {
                            $productIds[] = $newSku[$data['_super_products_sku']][$this->getProductEntityLinkField()];
                        } elseif (isset($oldSku[$data['_super_products_sku']])) {
                            $productIds[] = $oldSku[$data['_super_products_sku']][$this->getProductEntityLinkField()];
                        }
                    }
                }
            }

            foreach ($this->_productColFac->create()->addFieldToFilter(
                'type_id',
                $this->_productTypesConfig->getComposableTypes()
            )->addFieldToFilter(
                $this->getProductEntityLinkField(),
                ['in' => $productIds]
            )->addAttributeToSelect(
                array_keys($this->_superAttributes)
            )->setFlag('has_stock_status_filter', true) as $product) {
                $attrSetName = $attrSetIdToName[$product->getAttributeSetId()];

                $data = array_intersect_key($product->getData(), $this->_superAttributes);
                foreach ($data as $attrCode => $value) {
                    $attrId = $this->_superAttributes[$attrCode]['id'];
                    $productId = $product->getData($this->getProductEntityLinkField());
                    $this->_skuSuperAttributeValues[$attrSetName][$productId][$attrId] = $value;
                }
            }
        }
        return $this;
    }

}
