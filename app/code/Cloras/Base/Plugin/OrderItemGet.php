<?php

namespace Cloras\Base\Plugin;

class OrderItemGet
{
    private $orderExtensionFactory;

    private $orderItemExtensionFactory;

    private $productRepository;

    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Magento\Sales\Api\Data\OrderItemExtensionFactory $orderItemExtensionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Cloras\Base\Repo\OrdersIndex $orderIndexRepository,
        \Cloras\Base\Helper\Data $clorasHelper
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
        $this->customerRepositoryFactory = $customerRepositoryFactory;
        $this->productRepositoryFactory = $productRepository;
        $this->orderIndexRepository = $orderIndexRepository;
        $this->clorasHelper = $clorasHelper;
    }


    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }

    /**
     * Get Cloras Orders Id.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $resultOrder
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        if (is_object($subject)) {
            $resultOrder = $this->getOrderItemData($resultOrder);
            $resultOrder = $this->getOrderPaymentData($resultOrder);
        }

        return $resultOrder;
    }


    public function getOrderPaymentData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        /***/
        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();

        $orderPayment = $order->getPayment()->getData();
        
        foreach ($orderPayment as $key => $value) {
            if ($key == 'cc_last_4') {
                $cardNo = (($value) ? $value : "");
                $orderExtension->setCard($cardNo);
            }

            if ($key == 'method') {
                if ($value == 'checkmo') {
                    $orderExtension->setPaymentType('Cash');
                } elseif ($value == 'chargecreditline') {
                    $orderExtension->setPaymentType('chargecreditline');
                } else {
                    $orderExtension->setPaymentType($value);
                }
            }

            if ($key == 'amount_ordered') {
                $paymentAmt = (($value) ? $value : 0);
                $orderExtension->setPaymentAmt($paymentAmt);
            }
            
            if ($key == "last_trans_id") {
                $transId = (($value) ? $value : 0);
                $orderExtension->setTransId($transId);
            }

            $orderExtension->setAuthorizeNo(0);
            if ($key =='additional_information') {
                if (array_key_exists('processorAuthorizationCode', $value)) {
                    if (!empty($value['processorAuthorizationCode'])) {
                        $orderExtension->setAuthorizeNo($value['processorAuthorizationCode']);
                    }
                }
                if (array_key_exists('cc_type', $value)) {
                    $paymentType = (($value['cc_type']) ? $value['cc_type'] : '');
                    $orderExtension->setPaymentType($paymentType);
                }
            }
        }
        $orderExtension = $this->getOrderExtentionData($order, $orderExtension);

        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * Get Cloras Order Id for items of order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrderItemData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        
        $orderItems = $order->getItems();
        if (null !== $orderItems) {
            /*
             * @var \Magento\Sales\Api\Data\OrderItemInterface
             */

            foreach ($orderItems as $orderItem) {
                $extensionAttributes = $orderItem->getExtensionAttributes();

                /**
                 * @var \Magento\Sales\Api\Data\OrderItemExtension
                 */
                $orderItemExtension = $extensionAttributes
                ? $extensionAttributes
                : $this->orderItemExtensionFactory->create();

                $orderItemExtension = $this->getOrderItemExtensionData($orderItem, $orderItemExtension);
           
                $orderItem->setExtensionAttributes($orderItemExtension);
            }
        }

        /***/
        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();

        $orderExtension = $this->getOrderExtentionData($order, $orderExtension);

        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    public function getOrderItemExtensionData($orderItem, $orderItemExtension)
    {
       
        $uom = '';
        $inv_mast_uid = '';
        $erp_product_id = '';

        if ($inv_mast_uid = $this->getProductAttributeValue($orderItem->getSku(), 'inv_mast_uid')) {
            $orderItemExtension->setInvMastUid($inv_mast_uid);
        }

        if ($uom = $this->getProductAttributeValue($orderItem->getSku(), 'uom')) {
            $orderItemExtension->setUom($uom);
        }

        if ($erp_product_id = $this->getProductAttributeValue($orderItem->getSku(), 'erp_product_id')) {
            $orderItemExtension->setErpProductId($erp_product_id);
        }
        
        
        return $orderItemExtension;
    }

    public function getOrderExtentionData($order, $orderExtension)
    {
       /* set Custom Order Extension */
        $orderExtension->setSalesLocationId('81210');//Default sales location for Guest user
        if ($order->getCustomerId()) {
            if ($clorasERPCustomerId = $this->getCustomerAttributeValue(
                $order->getCustomerId(),
                'cloras_erp_customer_id'
            )) {
                $orderExtension->setClorasERPCustomerId($clorasERPCustomerId);
            }

            if ($clorasERPContactId = $this->getCustomerAttributeValue($order->getCustomerId(), 'cloras_erp_contact_id')
            ) {
                $orderExtension->setClorasERPContactId($clorasERPContactId);
            }

            if ($clorasERPShiptoId = $this->getCustomerAttributeValue($order->getCustomerId(), 'cloras_erp_shipto_id')
            ) {
                $orderExtension->setClorasERPShiptoId($clorasERPShiptoId);
            }
        
            if ($salesLocation = $this->getCustomerAttributeValue($order->getCustomerId(), 'sales_location')
            ) {
                $orderExtension->setSalesLocationId($salesLocation);
            }
        }

        return $orderExtension;
    }
    

    private function getCustomerAttributeValue($customerId, $attributeCode)
    {
        try {
            $customerRepository = $this->customerRepositoryFactory->create();
            $customers = $customerRepository->getById($customerId);
            if (is_object($customers->getCustomAttribute($attributeCode))) {
                return $customers->getCustomAttribute($attributeCode)->getValue();
            }
        } catch (\Exception $e) {
            $customers = false;
        }

        return '';
    }

    private function getProductAttributeValue($sku, $attributeCode)
    {
        try {
            $products = $this->productRepositoryFactory->get($sku);
            if (is_object($products->getCustomAttribute($attributeCode))) {
                return $products->getCustomAttribute($attributeCode)->getValue();
            }
        } catch (\Exception $e) {
            $products = false;
        }

        return '';
    }

    /**
     * @param  \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param  \Magento\Sales\Api\Data\OrderInterface $result
     * @return mixed
     * @throws \Exception
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        $result
    ) {
        if (is_object($subject)) {
            if ($result->getState() == \Magento\Sales\Model\Order::STATE_CANCELED) {
                if ($result->getId()) {
                    $this->orderIndexRepository->deleteOrderById($result->getId());
                }
            }
        }
        
        return $result;
    }
}
