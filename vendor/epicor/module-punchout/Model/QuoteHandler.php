<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;

use Epicor\Comm\Helper\Locations;
use Epicor\Comm\Helper\Product;
use Epicor\Punchout\Api\Data\Order\PurchaseOrderInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Quote to order.
 *
 */
class QuoteHandler
{

    /**
     * @var CartManagementInterface
     */
    private $cartManagementInterface;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepositoryInterface ;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * Data array.
     *
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $activeListIds;


    /**
     * Constructor.
     *
     * @param CartManagementInterface    $cartManagementInterface Cart management interface.
     * @param CartRepositoryInterface    $cartRepositoryInterface Cart repository interface.
     * @param ProductRepositoryInterface $productRepository       Product repository interface.
     * @param OrderResource              $orderResource           Order resource.
     * @param array                      $data                    Data array.
     *
     */
    public function __construct(
        CartManagementInterface $cartManagementInterface,
        CartRepositoryInterface $cartRepositoryInterface,
        ProductRepositoryInterface $productRepository,
        OrderResource $orderResource,
        array $data
    ) {
        $this->cartManagementInterface = $cartManagementInterface;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->productRepository       = $productRepository;
        $this->orderResource           = $orderResource;
        $this->data                    = $data;

    }//end __construct()


    /**
     * Create empty cart.
     *
     * @return CartInterface
     * @throws CouldNotSaveException Exception.
     * @throws NoSuchEntityException Exception.
     */
    public function createQuote()
    {
        $cartId = $this->cartManagementInterface->createEmptyCart();

        return $this->cartRepositoryInterface->get($cartId);

    }//end createQuote()


    /**
     * Create and add items to quote.
     *
     * @param $customer Customer Model.
     * @param PurchaseOrderInterface $orderInstance Order instance.
     * @param StoreInterface $store Store interface.
     *
     * @return CartInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function updateQuote($customer, PurchaseOrderInterface $orderInstance, StoreInterface $store)
    {
        $quote = $this->createQuote();
        //set store.
        $quote->setStore($store);
        //set base currency code.
        $quote->setCurrencyCode($store->getBaseCurrencyCode());
        //assign customer.
        $quote->assignCustomer($customer);

        //add items to quote
        $items = $orderInstance->getItemArray();
        foreach ($items as $item) {
            $item    = json_decode($item, true);
            $options = [];

            //If product is not there in ECC, we still process the order.
            $product = $this->getProduct($item['sku'], $store->getId());
            if (!$product) {
                continue;
            }

            $customerModel = $this->data['customerModel']->load($customer->getId());
            $this->data['customerSessionObj']->setCustomer($customerModel);
            $this->data['customerSessionObj']->setIsPunchout(1);
            $this->getActiveListIds();
            $this->data['customerSessionObj']->unsCustomer();
            $this->data['customerSessionObj']->unsIsPunchout();

            if (!$this->canProceedwithSku($item, $product)) {
                continue;
            }

            if ($product->getTypeId() == 'grouped' && $item['uom']) {
                $delimiter = $this->data['commonHelper']->getUOMSeparator();
                $childSku  = $item['sku'] . $delimiter . $item['uom'];
                $product   = $this->getProduct($childSku, $store->getId());
                if (!$product) {
                    continue;
                }
            }

            $options['uom'] = $item['uom'];
            $options['qty'] = $this->getCorrectOrderQty($product, $item['qty'], $item['locationCode']);
            $options['location_code'] = $item['locationCode'];

            if ($product->getEccConfigurator()) {
                $options['custom_options'] = ['ewa_code' => $item['ewaCode']];
            }

            $quote->addLine($product, $options);

        }//end foreach

        return $quote;

    }//end updateQuote()


    /**
     * Get correct qty based on location
     *
     * @param ProductInterface $product Product.
     * @param integer $qty Quantity.
     * @param string $locationCode Location Code.
     *
     * @return integer
     */
    public function getCorrectOrderQty(ProductInterface $product, $qty, $locationCode)
    {
        $isLocation = $this->data['commLocationsHelper']->isLocationsEnabled();
        if ($isLocation && $locationCode) {
            $proHelper = $this->data['commProductHelper'];
            $newQty    = $proHelper->getCorrectOrderQty($product, $qty, $isLocation, $locationCode);
            if ($newQty['qty'] != $qty) {
                return $newQty['qty'];
            }
        }

        return $qty;

    }//end getCorrectOrderQty()


    /**
     * Save purchase order ref number.
     *
     * @param OrderInterface|null $order Order.
     * @param $orderId
     *
     * @return OrderInterface|null
     * @throws \Exception
     */
    public function saveOrderRef(?OrderInterface $order, $orderId)
    {
        $order->setEccPunchoutOrderRef($orderId);
        $this->orderResource->saveAttribute($order, 'ecc_punchout_order_ref');

        return $order;

    }//end saveOrderRef


    /**
     * Handle error.
     *
     * @param OrderInterface|null $order
     * @param PurchaseOrderInterface|null $orderInstance
     * @param mixed $message
     * @param bool $proceedWithIdentification
     *
     * @return
     */
    public function ManageErrors($order, $orderInstance, $message = [], $proceedWithIdentification = true)
    {
        return $this->data['ChangeHandler']->handleChanges($order, $orderInstance, $message, $proceedWithIdentification);

    }//end ManageErrors()


    /**
     * Can Sku be processed.
     *
     * @param $item
     * @param ProductInterface $product
     *
     * @return boolean
     */
    public function canProceedwithSku($item, ProductInterface $product)
    {
        $canProcess = true;

        //Is product available in given location.
        if ($item['locationCode'] && !$product->isValidLocation($item['locationCode'])) {
            $canProcess = false;
        }

        //Is product of type configurable.
        if ($product->getTypeId() == 'configurable') {
            $canProcess = false;
        }

        //Is product of type configurator without ewa_code option.
        if ($product->getEccConfigurator() && empty($item['ewaCode'])) {
            $canProcess = false;
        }

        if (!empty($this->activeListIds) && !in_array($product->getId(), $this->activeListIds)) {
            $canProcess = false;
        }

        return $canProcess;

    }//end canProceedwithSku()


    /**
     * get Active List product Ids.
     */
    public function getActiveListIds()
    {
        $helper         = $this->data['listsFrontendProductHelper'];
        $contractHelper = $this->data['listsFrontendContractHelper'];
        if ($helper->listsDisabled()) {
            return $this->activeListIds;
        }

        if ($helper->hasFilterableLists() || $contractHelper->mustFilterByContract()) {
            $this->activeListIds = explode(',', $helper->getActiveListsProductIds());
        }

    }//end getActiveListIds()


    /**
     * Get product object.
     *
     * @param string $sku
     * @param integer $storeId
     *
     * @return bool|ProductInterface
     */
    private function getProduct(string $sku, int $storeId)
    {
        try {
            $product = $this->productRepository->get($sku, false, $storeId, true);
        } catch (\Exception $e) {
            $product = false;
        }

        return $product;

    }//end getProduct()


}//end class