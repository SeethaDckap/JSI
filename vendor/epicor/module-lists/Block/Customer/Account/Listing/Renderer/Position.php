<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Listing\Renderer;


use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Position extends AbstractRenderer
{
    /**
     * @var \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products
     */
    private $products;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products $products = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->products = $products;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $entity_id = $row->getEntityId();
        return '<div class="admin__grid-control">
                    <input 
                        type="text" 
                        class="input-text validate-number" 
                        data-entity-id="'.$row->getEntityId().'" 
                        name="position" 
                        ' . $this->getEnabledDisabled($row) . ' 
                        value="'.$row->getListPosition().'">
                  </div>';
    }

    /**
     * @param $row
     * @return string
     */
    private function getEnabledDisabled($row)
    {
        if (!$this->isProductSelectedInGrid($row)) {
            return 'disabled="disabled"';
        }
    }

    /**
     * @param $row
     * @return bool
     */
    private function isProductSelectedInGrid($row)
    {
        if (!$row instanceof \Magento\Framework\DataObject) {
            return false;
        }
        $sku = $row->getData('sku');

        $selectedProducts = $this->getSelectedProducts();

        if (is_array($selectedProducts)) {
            return in_array($sku, $selectedProducts, true);
        }
    }

    /**
     * @return array
     */
    private function getSelectedProducts()
    {
        $requestedProductsSkuKeys = $this->getRequestProductSkuValues();
        $selectedProductSkuKeys = $this->getSelectedSkuKeys();
        if ($requestedProductsSkuKeys && $this->isGridUpdate()) {
            return $requestedProductsSkuKeys;
        }
        if ($this->products) {
            return array_merge($requestedProductsSkuKeys, $selectedProductSkuKeys);
        }
    }

    /**
     * @return bool
     */
    private function isGridUpdate()
    {
        $positionColumn = $this->getColumn();
        if ($positionColumn instanceof \Magento\Backend\Block\Widget) {
            return $positionColumn->getIsGridUpdate();
        }

        return false;
    }

    /**
     * @return array
     */
    private function getRequestProductSkuValues()
    {
        $requestProducts = $this->getRequest()->getParam('products');
        if (is_array($requestProducts)) {
            return $requestProducts;
        }

        return [];
    }

    /**
     * @return array
     */
    private function getSelectedSkuKeys()
    {
        $list = $this->products->getList();
        $selected = [];
        foreach ($list->getProducts() as $product) {
            $sku = $product->getSku();
            $selected[] = $sku;
        }

        return $selected;
    }
}
