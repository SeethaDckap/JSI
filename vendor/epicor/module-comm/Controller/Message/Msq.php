<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Message;

use Epicor\Lists\Helper\Frontend\Product As ListProductHelper;

class Msq extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locHelper;

    /**
     * @var ListProductHelper
     */
    private $listsProdHelper;

    /**
     * @var array
     */
    private $listProducts = [];

    /**
     * Msq constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory
     * @param \Epicor\Comm\Helper\Messaging $commMessagingHelper
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Magento\Catalog\Model\ProductFactory $catalogProductFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Epicor\Comm\Helper\Locations $locHelper
     * @param ListProductHelper $listsProdHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Comm\Helper\Locations $locHelper,
        ListProductHelper $listsProdHelper
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commProductHelper = $commProductHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->eventManager = $context->getEventManager();
        $this->storeManager = $storeManager;
        $this->locHelper = $locHelper;
        $this->listsProdHelper = $listsProdHelper;
        parent::__construct(
            $context
        );
    }



    /**
     * Offline Orders Test/Trigger Action
     */
    public function execute()
    {
        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq \Epicor\Comm\Model\Message\Request\Msq */
        $msq->setTrigger('API call');

        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        $hasErrors = false;
        $productHelper = $this->commProductHelper;
        /* @var $productHelper \Epicor\Comm\Helper\Product */
        $locHelper = $this->locHelper;
        /* @var $productHelper \Epicor\Comm\Helper\Locations */

        $currencyCode = $this->getRequest()->getParam('currency_code');
        $skuParam = (array) $this->getRequest()->getParam('sku');
        $idParam = (array) $this->getRequest()->getParam('id');
        $from = $this->getRequest()->getParam('from');
        $erpAccountId = $this->getRequest()->getParam('erp_account_id');
        $saveValues = $this->getRequest()->getParam('save_values');
        $dontProcess = $this->getRequest()->getParam('dont_process');
        $qty = $this->getRequest()->getParam('qty');
        $ewaCode = $this->getRequest()->getParam('ewa');
        $attributes = $this->getRequest()->getParam('att');
        $useIndex = $this->getRequest()->getParam('use_index');
        $offline = $this->getRequest()->getParam('offline');

        $skuList = !empty($skuParam) ? $skuParam : $idParam;
        $productsCount = max(count($skuParam), count($idParam));

        $productsArray = array();
        for ($i = 0; $i < $productsCount; $i++) {
            if (isset($skuParam[$i])) {
                $productsArray[$i]['sku'] = $skuParam[$i];
            }
            if (isset($idParam[$i])) {
                $productsArray[$i]['id'] = $idParam[$i];
            }
        }

        $productsToSend = array(); 
        $qtys = array();
        $productsNotSent = array();
        $products = array();

        if (!empty($currencyCode)) {
            $msq->addCurrency($currencyCode);
        }

        if (!empty($erpAccountId)) {
            $msq->setCustomerGroupId($erpAccountId);
            if ($offline) {
                /*Not yet implemented in M2, in context to 4489
                    $msq->setOfflineShippingAddress();
                */
                $msq->setUpdateGroupedProducts(true);
            }
        }

        if ($saveValues) {
            /*Not yet implemented in M2, in context to 4489
                $msq->setIsOfflineMsq(true);
            */
            $msq->setSaveProductDetails(true);
        }

        $currencyCode = $helper->getCurrencyMapping($currencyCode, \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);

        foreach ($productsArray as $index => $p) {
            $uomSeparator = $helper->getUOMSeparator();
            if (isset($p['sku']) && strpos($p['sku'], $uomSeparator) !== false) {
                $product = $helper->findProductBySku($helper->getSku($p['sku']), $helper->getUom($p['sku']), false);
            } else if (isset($p['id']) && !empty($p['id'])) {
                $product = $this->catalogProductFactory->create()->load($p['id']);
                if ($product->getTypeId() == 'grouped' && isset($p['sku'])) {
                    $product = $helper->findProductBySku($p['sku'], '', false);
                }
            } else if (isset($p['sku'])) {
                $product = $helper->findProductBySku($p['sku'], '', false);
            } else {
                continue;
            }

            /* @var $product \Epicor\Comm\Model\Product */

            if (!$product && isset($p['sku'])) {
                $product = $this->catalogProductFactory->create();
                $product->setSku($p['sku']);
            }

            $skuQty = (is_array($qty)) ? $qty[$index] : 1;
            $skuEwaCode = (is_array($ewaCode)) ? $ewaCode[$index] : '';

            $att = array();
            if (!empty($skuEwaCode)) {
                $att['Ewa Code'] = $skuEwaCode;
            }

            $productAttributes = (is_array($attributes) && isset($attributes[$index]) ? (array) unserialize(base64_decode($attributes[$index])) : array());
            if (!empty(($productAttributes))) {
                foreach ($productAttributes as $productAtt) {
                    $productAttCode = @$productAtt['description'];
                    $productAttValue = @$productAtt['value'];
                    $att[$productAttCode] = $productAttValue;
                }
                $product->setMsqAttributes($att);
            }
            $products[$index] = $product;

            $sendProduct = true;
            if ($from == 'rfq' && ($product->getTypeId() == 'configurable' || ($product->getTypeId() == 'grouped' && !$product->getEccStkType()))) {
                $sendProduct = false;
            }

            if ($product->getEccConfigurator() == 1 && (empty($skuEwaCode) && empty($att))) {
                $sendProduct = false;
            }

            if ($sendProduct) {
                //$productsToSend++;
                //$msq->addProduct($product, $skuQty);
                $productsToSend[$index] = $product;
                $qtys[$index] = $skuQty;
            } else {
                $productsNotSent[] = $index;
            }
        }

        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setProducts($productsToSend);
        $transportObject->setMessage($msq);
        $this->eventManager->dispatch('msq_sendrequest_before', array(
            'data_object' => $transportObject,
            'message' => $msq,
        ));
        $productsToSend = $transportObject->getProducts();
        foreach ($productsToSend as $index => $product) {
            $msq->addProduct($product, isset($qtys[$index]) ? $qtys[$index] : '');
        }
        $msq->setPreventRounding(true);

        $success = (count($productsToSend) > 0) ? $msq->sendMessage() : true;

        $this->eventManager->dispatch('msq_sendrequest_after', array(
            'data_object' => $transportObject,
            'message' => $msq,
        ));

        if (!empty($dontProcess)) {
            $this->getResponse()->setBody('');
            return;
        }

        $prodArray = array();
        $this->initListProducts();

        foreach ($skuList as $index => $key) {
            if(!empty($key)) {
                $product = $products[$index];
                /* @var $product \Epicor\Comm\Model\Product */
                $product_status = $product->getStatus();
                $skuQty = (is_array($qty)) ? $qty[$index] : 1;
                //ensure qty is not null
                $skuQty = $skuQty ? $skuQty : 1;
                $foundItem = $product->getId();

                if ($foundItem
                    && $this->listsCheck($useIndex, $foundItem)
                ) {
                    $price = $productHelper->getProductPrice($product, $skuQty);
                    if (!empty($price)) {
                        $formattedPrice = $helper->formatPrice($price, true, $currencyCode);
                        $formattedTotal = $helper->formatPrice($price * $skuQty, true, $currencyCode);

                        $product->setUsePrice($price);
                        $product->setMsqFormattedPrice($formattedPrice);
                        $product->setMsqFormattedTotal($formattedTotal);
                    }
                    if (!empty($price)) {
                        $dealerPrice = $productHelper->getdealerPrice($product, $skuQty);
                        $product->setdealerPrice($dealerPrice);
                    }

                    $product->setMsqQty($skuQty);
                    $infoArray = $productHelper->getProductInfoArray($product);
                    $infoArray['qty'] = $skuQty;
                    $infoArray['sendSku'] = $key;
                    $eccReturnType = $product->getResource()->getAttribute('ecc_return_type')->getFrontend()->getValue($product);
                    $infoArray['ecc_return_type_display'] = $eccReturnType;
                    $infoArray['ecc_return_type'] = '';
                    switch (true) {
                        case ($eccReturnType == 'Credit'):
                            $infoArray['ecc_return_type'] = 'C';
                            break;
                        case ($eccReturnType == 'Replace'):
                            $infoArray['ecc_return_type'] = 'S';
                            break;
                    }
                    $canShowOutOfStock = $locHelper->canShowOutOfStock($product);
                    if ((!$success || !$foundItem || !$product->getIsSalable() || !$canShowOutOfStock) && !in_array($index, $productsNotSent)) {
                        if (!$canShowOutOfStock) {
                            $infoArray['status_error'] = 1;
                        } else {
                            $infoArray['error'] = 1;
                        }
                        $hasErrors = true;
                    }
                    if ($product_status == 2) {
                        $infoArray['status_error'] = 1;
                        $hasErrors = true;
                    }
                    if (!in_array($this->storeManager->getWebsite()->getId(), $product->getWebsiteIds())) {
                        $infoArray['status_error'] = 1;
                        $hasErrors = true;
                    }
                } else {
                    $infoArray = [];
                    $infoArray['sku'] = $key;
                    $infoArray['status_error'] = 1;
                    $infoArray['error'] = 1;
                    $hasErrors = true;
                }
                $key = ($useIndex == 'row_id' ? $index : $key);
                $prodArray[$key] = $infoArray;
            } else {
                $prodArray[] = array('error' => true, 'status_error' => true);
                $hasErrors = true;
            }
        }
        
        if ($hasErrors) {
            $prodArray['has_errors'] = 1; 
        }

        $response = json_encode($prodArray);

        $this->getResponse()->setBody($response);
    }

    /**
     * Set the list products
     */
    private function initListProducts()
    {
        if ($this->listsProdHelper->listsEnabled()
            && $this->listsProdHelper->hasFilterableLists()
        ) {
            $productIds = $this->listsProdHelper->getActiveListsProductIds();
            $this->listProducts = explode(',', $productIds);
        }
    }

    /**
     * Check if product is available when list enabled
     * @param string $useIndex
     * @param int $productId
     * @return bool
     */
    private function listsCheck($useIndex, $productId)
    {
        $valid = true;
        if (
            $useIndex == 'row_id'
            && $this->listsProdHelper->listsEnabled()
            && empty($this->listProducts) === false
        ) {
            $valid = (in_array($productId, $this->listProducts) === true) ?: false;
        }
        return $valid;
    }

}
