<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product;


class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /* Overwritten to be able to add custom columns to the product grid. Normally
     * one would overwrite the function _prepareCollection, but it won't work because
     * you have to call parent::_prepareCollection() first to get the collection.
     *
     * But since parent::_prepareCollection() also finishes the collection, the
     * joins and attributes to select added in the overwritten _prepareCollection()
     * are 'forgotten'.
     *
     * By overwriting setCollection (which is called in parent::_prepareCollection()),
     * we are able to add the join and/or attribute select in a proper way.
     *
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $websiteFactory,
            $setsFactory,
            $productFactory,
            $type,
            $status,
            $visibility,
            $moduleManager,
            $data
        );
    }


    public function setCollection($collection)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        $store = $this->_getStore();

        if ($store->getId() && !isset($this->_joinAttributes['ecc_uom'])) {
            $collection->joinAttribute(
                'ecc_uom', 'catalog_product/ecc_uom', 'entity_id', null, 'left', $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('ecc_uom');
        }

        $collection->setFlag('is_catalog_products_grid', true);

        parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $this->addColumnAfter('ecc_uom', array(
            'header' => __('UOM'),
            'type' => 'ecc_uom',
            'index' => 'ecc_uom',
            ), 'sku'
        );

        return parent::_prepareColumns();
    }

}
