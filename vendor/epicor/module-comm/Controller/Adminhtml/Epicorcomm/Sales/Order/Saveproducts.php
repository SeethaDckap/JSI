<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order;

class Saveproducts extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $salesOrderItemFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Sales\Model\Order\ItemFactory $salesOrderItemFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->registry = $registry;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->salesOrderItemFactory = $salesOrderItemFactory;
    }
    public function execute()
    {
        $order_id = $this->getRequest()->get('order_id');
        if ($order_id) {
            $confirmed = $this->getRequest()->get('confirmed');
            $product_data = $this->getRequest()->get('products');
            $products = json_decode($product_data);

            $order = $this->salesOrderFactory->create()->load($order_id);
            /* @var $order Mage_Sales_Model_Order */

            $additional_grand_total = 0;
            foreach ($products as $product_id => $product_data) {
                $qty = $product_data->qty;
                $price = $product_data->custom_price;
                $row_total = $qty * $price;
                $additional_grand_total += $row_total;
            }

            $maxCharge = $this->_getHelper()->getMaxAdditionalCharge($order);
            $maxTotal = $order->getGrandTotal() + $maxCharge;
            $newTotal = $order->getGrandTotal() + $additional_grand_total;

            if (!$confirmed) {
                $return_data = array(
                    'additional_amount' => $additional_grand_total,
                    'orig_grand_total' => $order->getGrandTotal(),
                    'new_grand_total' => $newTotal,
                    'max_grand_total' => $maxTotal,
                    'grand_total_diff' => $maxTotal - $newTotal,
                    'valid_amendment' => ($newTotal - $maxTotal) < 0.01,
                );
                $this->registry->register('return_data', $return_data);
                $products_data = array();
                foreach ($products as $product_id => $product_values) {
                    $product = $this->catalogProductFactory->create()->load($product_id);
                    /* @var $product Mage_Catalog_Model_Product */
                    $products_data[$product_id] = $this->dataObjectFactory->create(array(
                        'id' => $product_id,
                        'price' => $product_values->custom_price,
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'qty' => $product_values->qty,
                        'subtotal' => $product_values->qty * $product_values->custom_price
                    ));
                }
                sort($products_data);
                $this->registry->register('products', $products_data);

                $return_data['html'] = $this->getLayout()->createBlock('epicor_comm/adminhtml_sales_order_view_addproducts_summary')->toHtml();
                echo json_encode($return_data);
            } else {
//                $this->helper('tax')->getPrice($_product, $_product->getPrice(), true);

                foreach ($products as $product_id => $product_data) {

                    $product = $this->catalogProductFactory->create()->load($product_id);
                    /* @var $product Mage_Catalog_Model_Product */

                    $qty = $product_data->qty;
                    $price = $product_data->custom_price;
                    $row_total = $qty * $price;

                    $order_item = $this->salesOrderItemFactory->create()
                        ->setStoreId(NULL)
                        ->setQuoteItemId(NULL)
                        ->setQuoteParentItemId(NULL)
                        ->setProductId($product_id)
                        ->setProductType($product->getTypeId())
                        ->setQtyBackordered(NULL)
                        ->setTotalQtyOrdered($qty)
                        ->setQtyOrdered($qty)
                        ->setName($product->getName())
                        ->setSku($product->getSku())
                        ->setPrice($price)
                        ->setBasePrice($price)
                        ->setOriginalPrice($price)
                        ->setRowTotal($row_total)
                        ->setBaseRowTotal($row_total)
                        ->setOrder($order);

                    echo "Saving order item...\n";
                    $order_item->save();
                }
                $order->setBaseSubtotal($order->getBaseSubtotal() + $additional_grand_total);
                $order->setSubtotal($order->getSubtotal() + $additional_grand_total);
                $order->setBaseGrandTotal($order->getBaseGrandTotal() + $additional_grand_total);
                $order->setGrandTotal($order->getGrandTotal() + $additional_grand_total);
                $payment = $order->getPayment();
                $payment->setAmountOrdered($payment->getAmountOrdered() + $additional_grand_total);
                $payment->setBaseAmountOrdered($payment->getBaseAmountOrdered() + $additional_grand_total);
                $payment->save();
                $order->save();
                $order->sendOrderUpdateEmail(true, 'New Item/s have been added to your order');
            }
        }
    }

    }
