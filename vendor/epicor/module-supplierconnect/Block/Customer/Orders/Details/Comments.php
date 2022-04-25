<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details;


class Comments extends \Epicor\Supplierconnect\Block\Customer\Info
{
    public function _construct()
    {
        parent::_construct();


        $orderMsg = $this->registry->registry('supplier_connect_order_details');

        if ($orderMsg) {

            $order = $orderMsg->getPurchaseOrder();

            if ($order) {

                $orderDisplay = $this->registry->registry('supplier_connect_order_display');

                if ($orderDisplay == 'edit') {
                    $comment = '<textarea id="supplier_connect_order_comments" name="purchase_order[comment]" cols="165" rows="4">' . $order->getComment() . '</textarea>';
                } else {
                    $comment = $order->getComment();
                }

                $this->_infoData = array();

                $this->_extraData = array(
                    '' => $comment
                );
            }
        }

        $this->setTitle(__('Comments'));
        $this->setColumnCount(1);
    }

}
