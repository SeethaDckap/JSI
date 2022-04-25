<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\Product;


/**
 * Model Resource Class for Contracts
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    public function _getItemId(\Magento\Framework\DataObject $item)
    {
        if($this->getFlag('allow_duplicate') && isset($this->_items[$item->getId()])){
            return $item->getId().uniqid();
        }
    }

    /**
     * @param \Epicor\Lists\Model\ListModel $list
     */
    public function setListProductsByPosition(\Epicor\Lists\Model\ListModel $list)
    {
        $this->addAttributeToSelect('list_position');
        $this->setFlag('allow_duplicate', 1);
        $this->getSelect()->joinLeft(
            ['lp' => $this->getTable('ecc_list_product')],
            'e.sku = lp.sku AND lp.list_id = "' . $list->getId() . '"',
            ['list_position']
        );

        //If the type is contract then search for the product
        if ($list->getType() === 'Co') {
            $this->joinContractProducts();
        }

        $this->getSelect()->order(new \Zend_Db_Expr("-lp.list_position desc"));
        $this->getSelect()->order("e.entity_id desc");
    }

    /**
     * Join for contract products
     */
    private function joinContractProducts()
    {
        $this->getSelect()->joinLeft(
            array('cp' => $this->getTable('ecc_contract_product')),
            'cp.list_product_id =lp.id',
            [
                'start_date',
                'line_number',
                'part_number',
                'end_date',
                'status',
                'is_discountable',
                'min_order_qty',
                'max_order_qty'
            ]
        );
    }
}
