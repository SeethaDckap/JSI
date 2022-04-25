<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Listing\Renderer;


/**
 * Is grouped part link grid renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Epicor\Lists\Block\Customer\Account\Listing\Products\Grid
     */
    private $productsGrid;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Epicor\Lists\Block\Customer\Account\Listing\Products\Grid $productsGrid = null,
        array $data = []
    )
    {
        $this->_storeManager = $_storeManager;
        parent::__construct(
            $context,
            $data
        );
        $this->productsGrid = $productsGrid;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $index = $this->getColumn()->getIndex();
        $html = '<input type="text" name="qty" value="' . $row->getData($index) . '" class="input-text ">';
        if ($row->getTypeId() == 'grouped' || $row->getTypeId() == 'configurable' || !$this->isProductSelected($row)) {
            $html = '<input type="text" name="qty" value=0 class="input-text " disabled>';
        }
        return $html;
    }

    /**
     * @param $row
     * @return bool
     */
    private function isProductSelected($row)
    {
        if (!$row instanceof \Magento\Framework\DataObject) {
            return false;
        }
        $sku = $row->getData('sku');

        return $this->isSelectedInGrid($sku);
    }

    /**
     * @param $sku
     * @return bool
     */
    private function isSelectedInGrid($sku)
    {
        if ($this->productsGrid) {
            $selected = $this->productsGrid->getSelected();
            return is_array($selected) && array_key_exists($sku, $selected);
        }

        return false;
    }

}
