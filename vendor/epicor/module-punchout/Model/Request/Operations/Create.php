<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Request\Operations;

use Magento\Customer\Model\Session;
use Magento\Quote\Model\QuoteFactory;
use Epicor\Comm\Model\Message\Request\CdmFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;

/**
 * Class Create
 *
 * @package Epicor\Punchout\Model\Request\Operations
 */
class Create extends CartOperation
{

    /**
     * MessageManager
     *
     * @var \Epicor\Punchout\Model\Request\Operations\messageManager
     */
    private $messageManager;

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * $cartRepositoryInterface
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepositoryInterface;

    /**
     * $cartManagementInterface
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagementInterface;

    /**
     * CustomerRepository
     *
     * @var $customerRepository
     */
    private $customerRepository;

    /**
     * Registry.
     *
     * @var \Epicor\Punchout\Model\Request\Operations\Magento\Framework\Registry
     */
    private $registry;

    /**
     * QuoteFactory
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CdmFactory
     */
    private $cdmRequestFactory;

    /**
     * Create constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository Product Repository.
     * @param \Magento\Framework\Message\ManagerInterface     $messageManager    Message Manager.
     * @param \Magento\Quote\Model\QuoteFactory               $quoteFactory      Quote Factory.
     * @param \Magento\Framework\Registry                     $registry          Registry.
     * @param \Magento\Customer\Model\Session                 $customerSession   CustomerSession.
     * @param \Epicor\Comm\Model\Message\Request\CdmFactory   $commMessageRequestCdmFactory
     * @param array                                           $data              Data.
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ManagerInterface $messageManager,
        QuoteFactory $quoteFactory,
        Registry $registry,
        Session $customerSession,
        CdmFactory $commMessageRequestCdmFactory,
        array $data=[]
    ) {
        parent::__construct($productRepository, $data);
        $this->storeManager            = $this->getStoreManager();
        $this->cartRepositoryInterface = $this->getCartRepository();
        $this->cartManagementInterface = $this->getCartManagementInterface();
        $this->customerRepository      = $this->getCustomerRepository();
        $this->messageManager          = $messageManager;
        $this->registry                = $registry;
        $this->quoteFactory            = $quoteFactory;
        $this->data                    = $data;
        $this->customerSession         = $customerSession;
        $this->cdmRequestFactory       = $commMessageRequestCdmFactory;

    }//end __construct()


    /**
     * Create a cart for Punchout session.
     *
     * @param array $itemData Item Data.
     * @param $customerId
     * @param $identity
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createCart($itemData, int $customerId, $identity)
    {
        $customerModel = $this->data['customerModelObject']->load($customerId);
        $this->customerSession->setCustomer($customerModel);
        $error        = 0;
        $errorMessage = '';
        $store        = $this->storeManager->getStore();
        $storeId      = $store->getId();
        $quote        = $this->createNewQuote($customerId, $store);

        // Add items in quote.
        if (!empty($itemData)) {
            foreach ($itemData as $item) {
                try {
                    if (!empty($this->addProductToCart($item, $storeId, $quote))) {
                        $productCart['notAddedProd'][] = $this->addProductToCart($item, $storeId, $quote);
                    }
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $error        = 1;
                }//end try
            }//end foreach
        }//end if

        $erpAccts = $customerModel->getErpAcctCounts();
        if (is_array($erpAccts) && count($erpAccts) > 1) {
            $erpAccount              = $this->data['commHelper']->getErpAccountByAccountNumber($identity);
            $erpAccountId            = $erpAccount->getId();
            $shippingAddressToUpdate = $this->data['commMessagingHelper']->getDefaultShippingAddress($erpAccountId);
            $quote->setEccErpAccountId($erpAccountId);
            $this->customerSession->setMasqueradeAccountId($erpAccountId);
        } else {
            $shippingAddressToUpdate = $customerModel->getDefaultShippingAddress();
            $quote->setEccErpAccountId($customerModel->getEccErpaccountId());
        }

        if (count($shippingAddressToUpdate->getData()) > 0) {
            $quote->getShippingAddress()->addData($shippingAddressToUpdate->getData());
        }

        $quote->setIsPunchout(1)->setIsActive(0)->save();
        $productCart['cartId']        = $quote->getId();
        $productCart['error']         = $error;
        $productCart['error_message'] = $errorMessage;
        $this->customerSession->unsCustomer($customerModel);

        return $productCart;

    }//end createCart()


    /**
     * @param int $cartId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function loadCart(int $cartId)
    {
        $quote = $this->quoteFactory->create()->load($cartId);

        if ($quote->getIsPunchout()) {
            $quote->setIsActive(1)->collectTotals()->save();
        }

    }//end loadCart()


    /**
     * Create New Quote.
     *
     * @param integer $customerId Customer Id.
     * @param integer $store      Store Id.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException Could Not Save Exception.
     * @throws \Magento\Framework\Exception\NoSuchEntityException No Such Entity Exception.
     */
    public function createNewQuote($customerId, $store)
    {
        $this->registry->register('dont_send_bsv', true, true);
        $cartId = $this->cartManagementInterface->createEmptyCart();
        $quote  = $this->cartRepositoryInterface->get($cartId);
        $quote->setStore($store);
        $customer = $this->customerRepository->getById($customerId);
        $quote->setCurrencyCode($store->getBaseCurrencyCode());
        $quote->assignCustomer($customer);
        return $quote;

    }//end createNewQuote()


    /**
     * Add Product to Cart.
     *
     * @param array                      $item    CXML request data.
     * @param integer                    $storeId Store Id.
     * @param \Magento\Quote\Model\Quote $quote   Quote object.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException No Such Entity Exception.
     */
    public function addProductToCart($item, $storeId, $quote)
    {
        $options = [];
        $product = $this->getProductData($item, $storeId);
        if (!empty($product) && $product->isSaleable()) {
            $this->data['quoteHandlerObject']->getActiveListIds();
            $canAddedToCart      = $this->canAddToCart($product, $item);
            $itemDetailExtrinsic = $this->getExtrinsicData($item->ItemDetail->Extrinsic);
            if ($product->getEccConfigurator() && !empty($itemDetailExtrinsic['ewaCode'])) {
                $configuratorAdded = $this->handleConfiguratorProduct($product, $item, $itemDetailExtrinsic['ewaCode'], $quote, $itemDetailExtrinsic['locationCode']);
                if ($configuratorAdded['canAdded']) {
                    $canAddedToCart = true;
                    $options = $configuratorAdded['options'];
                }
            }
            if ($canAddedToCart) {
                $qty = '1';
                if (!empty($item->attributes()->quantity)) {
                    $qty = (string) $item->attributes()->quantity;
                }

                $options['qty']           = $this->data['quoteHandlerObject']->getCorrectOrderQty($product, $qty, $itemDetailExtrinsic['locationCode']);
                $options['location_code'] = $itemDetailExtrinsic['locationCode'];
                $quote->addLine($product, $options);
            }
        }//end if

        return $canAddedToCart['notAddedProduct'];

    }//end addProductToCart()


    /**
     * Handle configurator Product.
     *
     * @param \Magento\Catalog\Model\Product $productData  Product Data.
     * @param array                          $item         CXML request data.
     * @param string                         $ewaCode      EwaCode for configurator.
     * @param \Magento\Quote\Model\Quote     $quote        Quote object.
     * @param string                         $locationCode Product locationCode.
     *
     * @return array
     */
    public function handleConfiguratorProduct($productData, $item, $ewaCode, $quote, $locationCode)
    {
        $canAddProduct  = false;
        $options        = [];
        $cdm            = $this->cdmRequestFactory->create();
        $qty            = (!empty($item->attributes()->quantity)) ? (string) $item->attributes()->quantity : '1';
        $options['qty'] = $this->data['quoteHandlerObject']->getCorrectOrderQty($productData, $qty, $locationCode);
        $cdm->setProductSku($productData->getSku());
        $cdm->setProductUom($productData->getEccUom());
        $cdm->setTimeStamp(null);
        $cdm->setQty($qty);
        $cdm->setEwaCode($ewaCode);
        $cdm->setQuoteId(!empty($quote->getId()) ? $quote->getId() : null);
        if ($cdm->sendMessage()) {
            $configurator        = $cdm->getResponse()->getConfigurator();
            $ewaTitle            = $configurator->getTitle();
            $ewaShortDescription = $configurator->getShortDescription();
            $ewaDescription      = $configurator->getDescription();
            $ewaSku              = $configurator->getConfiguredProductCode();
            $ewaAttributes = [
                [
                    'description' => 'Ewa Code',
                    'value'       => $ewaCode,
                ],
                [
                    'description' => 'Ewa Description',
                    'value'       => $ewaDescription,
                ],
                [
                    'description' => 'Ewa Short Description',
                    'value'       => $ewaShortDescription,
                ],
                [
                    'description' => 'Ewa SKU',
                    'value'       => $ewaSku,
                ],
                [
                    'description' => 'Ewa Title',
                    'value'       => $ewaTitle,
                ],
            ];
            $productData->setEwaCode($ewaCode);
            $productData->setEwaSku($ewaSku);
            $productData->setEwaDescription(base64_encode($ewaDescription));
            $productData->setEwaShortDescription(base64_encode($ewaShortDescription));
            $productData->setEwaTitle(base64_encode($ewaTitle));
            $productData->setEwaAttributes(base64_encode(serialize($ewaAttributes)));
            $ewaSkuOptionsId      = 0;
            $ewaCodeOptionId      = 0;
            $ewaDescOptionId      = 0;
            $ewaTitleOptionsId    = 0;
            $ewaShortDescOptionId = 0;
            foreach ($productData->getOptions() as $option) {
                if ($option->getType() === 'ewa_code') {
                    $ewaCodeOptionId = $option->getId();
                } else if ($option->getType() === 'ewa_description') {
                    $ewaDescOptionId = $option->getId();
                } else if ($option->getType() === 'ewa_short_description') {
                    $ewaShortDescOptionId = $option->getId();
                } else if ($option->getType() === 'ewa_title') {
                    $ewaTitleOptionsId = $option->getId();
                } else if ($option->getType() === 'ewa_sku') {
                    $ewaSkuOptionsId = $option->getId();
                }
            }

            $productData->setHasOptions(1);
            $ewaOptionsData            = [
                $ewaCodeOptionId      => $ewaCode,
                $ewaDescOptionId      => $ewaDescription,
                $ewaShortDescOptionId => $ewaShortDescription,
                $ewaTitleOptionsId    => $ewaTitle,
                $ewaSkuOptionsId      => $ewaSku,
            ];
            $options['custom_options'] = $ewaOptionsData;
            $canAddProduct             = true;

        }//end if

        return [
            'canAdded' => $canAddProduct,
            'options'  => $options,
        ];

    }//end handleConfiguratorProduct()


    /**
     * @param $product
     * @param $item
     *
     * @return array
     */
    public function canAddToCart($product, $item)
    {
        $notAddedProduct = null;
        $canAddProduct   = true;
        if (!empty($this->data['quoteHandlerObject']->activeListIds) && !in_array($product->getId(), $this->data['quoteHandlerObject']->activeListIds)) {
            $canAddProduct = false;
        }

        $itemDetailExtrinsic = $this->getExtrinsicData($item->ItemDetail->Extrinsic);
        //Is product available in given location.
        if (!empty($itemDetailExtrinsic['locationCode']) && !$product->isValidLocation($itemDetailExtrinsic['locationCode'])) {
            $notAddedProduct = $product->getName();
            $canAddProduct   = false;
        }

        if ($canAddProduct && $product->getEccConfigurator() && empty($itemDetailExtrinsic['ewaCode'])) {
            $notAddedProduct = $product->getName();
            $canAddProduct   = false;
        }

        return [
            'canAdded'        => $canAddProduct,
            'notAddedProduct' => $notAddedProduct,
        ];

    }//end canAddToCart()


    /**
     * @param \SimpleXMLElement $extrinsic
     *
     * @return array
     */
    public function getExtrinsicData(\SimpleXMLElement $extrinsic)
    {
        $locationCode = '';
        $ewaCode      = '';
        if (!empty($extrinsic)) {
            foreach ($extrinsic as $v) {
                if ((string) $v->attributes()['name'] === 'locationCode') {
                    $locationCode = (string) $v;
                }

                if ((string) $v->attributes()['name'] === 'ewaCode') {
                    $ewaCode = (string) $v;
                }
            }
        }

        return [
            'locationCode' => $locationCode,
            'ewaCode'      => $ewaCode,
        ];

    }//end getExtrinsicData()


}//end class
