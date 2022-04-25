<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogInventory\Helper\Data;
class NineONineBsvProductLine extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var boolean
     */
    private $isExceptionReturn = true;

    /**
     * NineONineBsvProductLine constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $prodLineError = array();

        //This is 909 status code check for Eclipse BSV Item Status
        //If the Url is checkout or CART and Backorder is Not Allowed
        $allowBackorder = $this->commHelper->getErpAccountInfo()->getAllowBackorders();
        $isCheckout = $this->checkCartOrOnePage();

        if (!$isCheckout || $this->registry->registry("isTriggerBsv909Error")) {
            //$this->checkoutSession->setNineZeroNine($throwError);
            return;
        }

        $bsv = $observer->getEvent()->getMessage();
        $quote = $bsv->getQuote();
        /* @var $quote \Epicor\Comm\Model\Quote */

        $lines = array();
        $response = $bsv->getResponse();
        if (isset($response['lines']) && isset($response['lines']['line']) && count($response['lines']['line']) > 0) {
            $lines = $bsv->_getGroupedDataArray('lines', 'line', $response);
        } else {
            return;
        }

        if ($quote) {
            foreach ($lines as $line) {
                if (isset($line['status']) && isset($line['status']['code']) && $line['status']['code'] == 909) {
                    if (isset($line['status']['description']) && $line['status']['description']) {
                        $message = $line['status']['description'];
                    } else {
                        $message = __('Order quantity %1 is not available by the Required Stock/Date.',
                            $line['productCode']);
                    }
                    $prodLineError[$line['productCode']] = $message;
                }
            }

            $items = $quote->getAllVisibleItems();
            if (count($items) > 0 && $prodLineError) {
                $processError = false;
                foreach ($items as $item) {
                    $options = $item->getOptionByCode("product_type");
                    if ($options && $options->getValue() == "grouped") { //For Group product display parent name for message
                        $productSku = $options->getProduct() ? $options->getProduct()->getSku() : $item->getSku();
                    } else {
                        $productSku = $item->getSku();
                    }
                    if (array_key_exists($productSku, $prodLineError)) {
                        $product = $item->getProduct();
                        /** @var \Epicor\Comm\Model\Product $product */
                        $isEccDiscontinued = $product ? $product->getIsEccDiscontinued() : 0;
                        if ($isEccDiscontinued || !$allowBackorder) {
                            $this->messageManager->getMessages(true, "quote_item" . $item->getId())->clear();
                            $this->messageManager->addErrorMessage($prodLineError[$productSku],
                                "quote_item" . $item->getId());
                            //$item->setMessage($prodLineError[$productSku]);
                            //$item->addErrorInfo('cataloginventory', 1, $prodLineError[$productSku]);
                            $processError = true;
                        }
                    }
                }
                if(!$processError) {
                    return;
                }
                //$this->checkoutSession->setNineZeroNine($prodLineError);
                $message = __('Some of the items will not be available by the required Stock/Date.');
                $quote->addErrorInfo(
                    'qty',
                    'cataloginventory',
                    Data::ERROR_QTY,
                    $message
                );
                $this->messageManager->getMessages(true, \Magento\Framework\Message\Manager::DEFAULT_GROUP)->clear();
                $this->messageManager->addErrorMessage($message);
                $this->registry->register('bsv_quote_error', $message);
                if ($this->isExceptionReturn) {
                    $this->isExceptionReturn = true;
                    throw new LocalizedException($message);
                }
            } else {
                //$this->checkoutSession->setNineZeroNine($prodLineError);
                //$quote->setRequiredDate("0000-00-00")->save();
                return;
            }
        }
        return;
    }

    /**
     * validate Checkout path
     *
     * @return bool
     */
    public function checkCartOrOnePage()
    {
        $module = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        /**
         * Some other below API or ajax should require in feature
         *
         * totals-information
         * estimate-shipping-methods-by-address-id
         * updateItemQty,couponPost
         * payment-information
         * set-payment-information
         */
        if ($this->strposa($this->request->getOriginalPathInfo(), [
                'shipping-information',
                'SaveBranchInformation',
                'loginPost',
                'updatePost',
                'estimate-shipping-methods-by-address-id',
                'updateItemQty'
                //'payment-information'
            ]) !== false) {

            if ($this->strposa($this->request->getOriginalPathInfo(), ['updateItemQty'])) {
                $this->isExceptionReturn = false;
            }
            return true;
        } else {
            if ($module === 'checkout' && $controller === 'cart' && $action === 'index') {
                $this->isExceptionReturn = false;
                return true;
            } else {
                if ($module == 'checkout' && $controller == 'onepage') {
                    $checkSuccess = ($action == "success") ? false : true;
                    return $checkSuccess;
                } else {
                    if ($module == 'checkout' && $controller == 'cart' && $action == 'updateRequireDate') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $haystack
     * @param $needle
     * @param int $offset
     * @return bool
     */
    private function strposa($haystack, $needle, $offset = 0)
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        foreach ($needle as $query) {
            if (strpos($haystack, $query, $offset) !== false) {
                return true;
            } // stop on first true result
        }
        return false;
    }
}