<?php
namespace Silk\SyncDecoder\Model\Epicor\Sales\Quote\Address\Total;

class Msq extends \Epicor\Comm\Model\Sales\Quote\Address\Total\Msq
{

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == 'shipping') {
            parent::collect($quote, $shippingAssignment, $total);

            if (false) {
                $this->sendMsqForCart($quote, $shippingAssignment->getShipping()->getAddress());
            }
        }
    }

    protected function sendMsqForCart($quote, $address)
    {
        return $this;
        if ($this->registry->registry('processed_items_after_msq') || $this->registry->registry('msq_sent')) {
            return $this;
        }
        /* @var $quote \Epicor\Comm\Model\Quote */
        $module = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $route = $this->request->getRouteName();
        // run an MSQ first to make sure we get the right prices

        $helper = $this->commConfiguratorHelper;
        /* @var $helper \Epicor\Comm\Helper\Configurator */

        if (
            !$this->registry->registry('bsv-processing') &&
            !($route == 'checkout' && $action == 'index') &&
            ($module != 'multishipping' || ($module == 'multishipping' && $controller == 'checkout' && $action == 'addressesPost')) &&
            !($quote->getEccQuoteId())
        ) {

            $helper->removeUnlicensedConfiguratorProducts($quote, false);

            $items = $this->getMsqItems($quote, $address);
            if ($this->registry->registry('csv_quickpad_send_msq')) {
                $skuslimit = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/quickpad_max_sku_in_msq', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $chunkproducts = array_chunk($items, $skuslimit);
                foreach ($chunkproducts as $chunkitems) {
                    $this->_sendMsqForBsvItems($quote, $chunkitems);
                    //$address = $this->resetAddressData($address);
                }
            } else {
                $this->_sendMsqForBsvItems($quote, $items);
                //$address = $this->resetAddressData($address);
            }
        }

        $cartItems = $this->_getCartItems($quote, $address);
        $session = $this->customerSessionFactory->create();
        if ($quote->getIsMultiShipping()) {
            $reg = $session->getCartMsqRegistry();
            //When we are using a operand It should be array.
            $reg += is_array($cartItems) ? $cartItems : array();
            $session->setCartMsqRegistry($reg);
        } else {
            $session->setCartMsqRegistry($cartItems);
        }

        return $this;
    }

    protected function _sendMsqForBsvItems(&$quote, &$items)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('triggered msq for bsv item');

        // to fix the issue of  at the time of logged it  took default  in MSQ
        if ($this->registry->registry('after_login_msq_init')) {
            $this->registry->unregister('after_login_msq');
            $this->registry->register('after_login_msq', 1);
            $this->registry->unregister('after_login_msq_init');
        }

        $msq = $this->commMessageRequestMsqFactory->create();
        /* @var $msq \Epicor\Comm\Model\Message\Request\Msq */

        $msqSuccessful = false;

        $basketItemsCache = $this->customerSession->getBasketItemsMsqData();

        if (!$this->registry->registry('SkipEvent') && count($items) > 0 && !$this->registry->registry('msq-processing')) {

            $this->registry->register('msq-processing', true);
            $this->registry->register('bsv-processing', true);

            if ($msq->isActive()) {
                $msq->setAllowPriceRules(false);
                $msq->setForceMsqPrices(true);
            }


            $products = [];
            $qtys = [];
            $msqlocations = [];

            foreach ($items as $x => $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */

                $attributes = $this->getItemAttributes($item);
                $tmpProduct = $this->catalogProductFactory->create();
                $tmpProduct->setData($item->getProduct()->getData());
                $tmpProduct->setMsqAttributes($attributes);
                $this->setProductMsqContract($item, $tmpProduct);
                $itemSku = $item->getSku();
                $productSku = $tmpProduct->getSku();
                $item->setProduct($tmpProduct);

                if ($option = $item->getOptionByCode('simple_product')) {
                    $products[$x] = $option->getProduct();
                } else {
                    $tmpProduct->setSku($itemSku);
                    $products[$x] = $tmpProduct;
                }

                $locationcode = $item->getEccLocationCode();
                if ($locationcode) {
                    $msqlocations[] = $locationcode;
                }
                $qtys[$x] = $item->getQty();
            }

            $transportObject = $this->dataObjectFactory->create();
            $transportObject->setProducts($products);
            $transportObject->setMessage($msq);
            $this->eventManager->dispatch('msq_sendrequest_before', array(
                'data_object' => $transportObject,
                'message' => $msq,
            ));
            $products = $transportObject->getProducts();

            if ($msq->isActive()) {
                $showLocations = $this->commLocationsHelper->isLocationsEnabled();
                if ($showLocations && !empty($msqlocations)) {
                    $msq->addLocations($msqlocations);
                }
                foreach ($products as $id => $product) {
                    $msq->addProduct($product, $qtys[$id]);
                }
                //Rounding must be done in the msq before the BSV
                $msq->setPreventRounding(true);
                $msqSuccessful = $msq->sendMessage();
            }

            $this->eventManager->dispatch('msq_sendrequest_after', array(
                'data_object' => $transportObject,
                'message' => $msq,
            ));
            $this->registry->unregister('msq_sent');
            $this->registry->register('msq_sent', true);
            $this->resetAddressData($quote->getShippingAddress());

            //In 2.3.1, lot of changes are made on quote repository level
            //Also sales_quote_save_after was changed
            if ($this->productMetadata->getVersion() > '2.3.0') {
                $this->registry->unregister('QuantityValidatorObserver');
                $this->registry->register('QuantityValidatorObserver', 1);
                $quote->save();
                $this->registry->unregister('QuantityValidatorObserver');
            } else {
                $quote->save();
            }

            if (!$quote->getEccQuoteId()) {
                $this->processItemsAfterMsq($quote, $items, $msqSuccessful);
                $this->registry->unregister('processed_items_after_msq');
                $this->registry->register('processed_items_after_msq', true);
            }

            $this->registry->unregister('msq-processing');
            $this->registry->unregister('bsv-processing');
            $this->checkoutSession->unsetData('bsv_sent_for_cart_page');
        }

        $this->processItemsAfterMsq($quote, $items, $msqSuccessful, true);

        return $msqSuccessful;
    }

    protected function processItems($quote, $items, $msqSuccessful, $setOriginalPrice)
    {
        $this->log('msq process item');
        $_fromCurr = $quote->getBaseCurrencyCode() ?: $this->storeManager->getStore()->getBaseCurrencyCode();
        $_toCurr = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $basketItemsCache = $this->customerSession->getBasketItemsMsqData();
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $linesCheckLogin = $contractHelper->linesCheckExistingProducts();
        if ($this->productMetadata->getVersion() > '2.3.0') {
            $greaterVersion = true;
        } else {
            $greaterVersion = false;
        }
        $successMessages = [];
        foreach ($items as $x => $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $item->setEccBsvPrice(null);
            $item->setEccBsvPriceInc(null);
            $item->setEccBsvLineValue(null);
            $item->setEccBsvLineValueInc(null);

            /**
             * For configurable product item get product should give null value
             * so getting MSQ ECC set product data Should Require to use get Option By Code
             */
            if ($option = $item->getOptionByCode('simple_product')) {
                $product = $option->getProduct();
            } else {
                $product = $item->getProduct();
            }
            //$product = $item->getProduct();
            $item->setEccMsqBasePrice($product->getEccMsqBasePrice());
            //catalog rule was applied and the action is "To fixed"
            $item->setHigherDiscount($product->getHigherDiscount());
            $item->setPromotionalAmount($product->getPromotionalAmount()); //promotional fixed amount
            $item->setOrdinaryCustomerAmount($product->getOrdinaryCustomerAmount()); //customer price

            if ($msqSuccessful) {
                $basketItemsCache[$item->getId()] = $product->debug();
            } else {
                if (isset($basketItemsCache[$item->getId()])) {
                    $product = $this->catalogProductFactory->create()->addData($basketItemsCache[$item->getId()]);
                }
            }
            //If there is price list rule applied then
            //Ignore location price
            $ignoreLocation = false;
            if ($product->getDiscountApplied() || $product->getPriceListApplied()) {
                $ignoreLocation = true;
            }

            //This was a patch given by Gareth for setting the location price
            //Previously this patch was not applied because bistrack was the one which supports multiple location price concept
            if ($item->getEccLocationCode() && (!$ignoreLocation)) {
                $staticLocationCode = (string)$item->getEccLocationCode();
                $product->setToLocationPrices($staticLocationCode);
                $product->setEccOriginalPrice(false);
                $product->unsFinalPrice();
                $product->setStaticLocationPrice(true);
                $item->setEccOriginalPrice($product->getEccOriginalPrice() ?: $product->calculateEpicorOriginalPrice($item->getQty()));
            } else {
                if (($greaterVersion && ($product->getDiscountApplied() || $product->getPriceListApplied())) || $this->registry->registry('cart_merged')) {
                    $item->setEccOriginalPrice($product->getEccOriginalPrice() ?: $product->calculateEpicorOriginalPrice($item->getQty()));
                }
            }
            if (!$quote->getEccQuoteId() && $contractHelper->contractsEnabled()) {
                $contractHelper->lineContractCheck($quote, $product, $item, $linesCheckLogin);
            }

            if ($setOriginalPrice) {
                /* @var $product Epicor_Comm_Model_Product */
                $item->setEccOriginalPrice($product->getEccOriginalPrice() ?: $product->calculateEpicorOriginalPrice($item->getQty()));
            } else if (!$quote->getEccQuoteId()) {
                $price = $product->getFinalPrice($item->getQty());
                $customPrice = $this->directoryHelper->currencyConvert($price, $_fromCurr, $_toCurr);
                $this->log('msq set custom price. Item: ' . $item->getSku() . '. Custom Price: ' . $customPrice);
                // $item->setCustomPrice($customPrice);
                // $item->setOriginalCustomPrice($customPrice);
                // $item->getProduct()->setIsSuperMode(true);
            }

            if ($contractHelper->contractsEnabled()) {
                $contractCode = $quote->getEccContractCode() ?: $item->getEccContractCode();

                $contractData = $item->getProduct()->getEccMsqContractData();

                if ($contractCode && $contractData) {
                    $contractQty = $this->getItemContractQty($contractCode, $contractData);

                    if ($contractQty > -1 && $item->getQty() > $contractQty) {
                        $message = __('The requested quantity for "%1" is not available. Only %2 available on the selected contract', $item->getProduct()->getName(), $contractQty);
                        $item = $this->processContractForItem($quote, $item, $message);
                        if (!$this->registry->registry('processed_items_after_msq')) {
                            $this->messageManager->addError($message);
                            continue;
                        }
                    }
                }
            }

            $msqAlwaysInStock = $this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            /**
             * First check discontinued is true then return false else
             * second return msq config always in stock
             */
            $alwaysInStock = $product->getIsEccDiscontinued() ? false : $msqAlwaysInStock; // discontinued item

            /**
             * First check non-stock is true then return true else
             * second return parent var alwaysInStock
             */
            $alwaysInStock = $product->getIsEccNonStock() ?: $alwaysInStock; // non-stock item
            if (!$alwaysInStock) {
                $item = $this->processStocksForItems($quote, $item, $product);
            }
            //Added to remove out of stock products from cart
            $remove = $this->registry->registry('hide_out_of_stock_product');
            if ($remove && in_array($item->getProductId(), $remove)) {
                $item = $this->processOutOfStockItems($quote, $item);
            }
            // needed for other modules to ensure discounts get calculated before bsv (ahem, amasty)
            if (!$quote->getIsMultiShipping()) {
                if($setOriginalPrice) {
                    $item->save();
                }
                if (!$quote->getItemsCollection()->getItemById($item->getId())) {
                    foreach ($quote->getItemsCollection()->getItems() as $id => $it) {
                        if ($it->getId() == $item->getId()) {
                            $quote->getItemsCollection()->removeItemByKey($id);
                        }
                    }
                    $quote->getItemsCollection()->addItem($item);
                }
                if ($setOriginalPrice && $greaterVersion) {
                    $quote->setTriggerRecollect(1);
                }
            }
            $ismultiplePId = $this->registry->registry('add_multiple_to_cart') ?:[];
            if(!$item->getErrorInfos() && $item->hasDataChanges() && array_key_exists($product->getId(), $ismultiplePId) ) {
                $options = $item->getOptionByCode("product_type");
                if ($options && $options->getValue() == "grouped") { //For Group product display parent name for message
                    $productName =  $options->getProduct() ? $options->getProduct()->getName() : $product->getName();
                    $productId = $options->getProduct() ? $options->getProduct()->getId() : $product->getId();
                    $successMessages[$productId] = __('%1 was successfully added to your shopping cart.', $productName);
                } elseif($item->getProduct()->getTypeId() == 'configurable') {
                    $productName =  $item->getProduct()->getName();
                    $successMessages[$item->getProduct()->getId()] = __('%1 was successfully added to your shopping cart.', $productName);
                } else {
                    $productName =  $product->getName();
                    $successMessages[$product->getId()] = __('%1 was successfully added to your shopping cart.', $productName);
                }
                //$this->messageManager->addSuccessMessage($message);
            }
        }

        if($successMessages) {
            foreach($successMessages as $message){
                $this->messageManager->addSuccessMessage($message);
            }
        }
        return;
    }

    private function log($message){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/observer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }

}
