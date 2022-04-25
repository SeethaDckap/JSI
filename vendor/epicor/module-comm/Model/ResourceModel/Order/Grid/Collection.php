<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 35)
namespace Epicor\Comm\Model\ResourceModel\Order\Grid;


class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    
    protected function _initSelect()
    {
         $this->addFilterToMap('entity_id', 'main_table.entity_id');
         $this->addFilterToMap('status', 'main_table.status');
         $this->addFilterToMap('store_id', 'main_table.store_id');
         $this->addFilterToMap('store_name', 'main_table.store_name');
         $this->addFilterToMap('customer_id', 'main_table.customer_id');
         $this->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
         $this->addFilterToMap('grand_total', 'main_table.grand_total');
         $this->addFilterToMap('total_paid', 'main_table.total_paid');
         $this->addFilterToMap('increment_id', 'main_table.increment_id');  
         $this->addFilterToMap('base_currency_code', 'main_table.base_currency_code');
         $this->addFilterToMap('order_currency_code', 'main_table.order_currency_code');
         $this->addFilterToMap('shipping_name', 'main_table.shipping_name');
         $this->addFilterToMap('billing_name', 'main_table.billing_name');
         $this->addFilterToMap('created_at', 'main_table.created_at');
         $this->addFilterToMap('updated_at', 'main_table.updated_at');
         $this->addFilterToMap('billing_address', 'main_table.billing_address');
         $this->addFilterToMap('shipping_address', 'main_table.shipping_address');
         $this->addFilterToMap('shipping_information', 'main_table.shipping_information');
         $this->addFilterToMap('customer_email', 'main_table.customer_email');
         $this->addFilterToMap('customer_group', 'main_table.customer_group');
         $this->addFilterToMap('subtotal', 'main_table.subtotal');
         $this->addFilterToMap('shipping_and_handling', 'main_table.shipping_and_handling');  
         $this->addFilterToMap('customer_name', 'main_table.customer_name');  
         $this->addFilterToMap('payment_method', 'main_table.payment_method');  
         $this->addFilterToMap('total_refunded', 'main_table.total_refunded');      
         parent::_initSelect();

        $this->getSelect()->joinLeft(
                ['sales_order' => $this->getTable('sales_order')],
                'main_table.entity_id = sales_order.entity_id',
                ['ecc_erp_order_number']
            );
        
       $this->getSelect()->joinLeft(
            array('osh' => $this->getTable('sales_order_status_history')),
            'osh.entity_id = (SELECT entity_id FROM ' . $this->getTable('sales_order_status_history') . ' WHERE parent_id=main_table.entity_id LIMIT 1)',
            array('ordercomment' => 'osh.comment')
        );

    
        return $this;
    }
}
//M1 > M2 Translation End