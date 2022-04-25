<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

class AjaxUpdate extends \Epicor\Comm\Controller\Cart
{

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    public function __construct(
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Comm\Helper\Product $commProductHelper
    ) {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->commProductHelper = $commProductHelper;
    }
    /**
     * Minicart ajax update qty action
     */
    public function execute()
    {
        if (!$this->_validateFormKey()) {
            throw new \Magento\Framework\Exception\LocalizedException('Invalid form key');
        }
        $id = (int) $this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty');
        $result = array();
        if ($id) {
            try {
                $cart = $this->_getCart();
                $quoteItem = $cart->getQuote()->getItemById($id);
                $product = $quoteItem->getProduct();
                if (isset($qty)) {
                    $filter = new \Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty = $filter->filter($qty);

                    $locHelper = $this->commLocationsHelper;
                    /* @var $locHelper Epicor_Comm_Helper_Locations */

                    $proHelper = $this->commProductHelper;
                    /* @var $proHelper Epicor_Comm_Helper_Product */

                    $locEnabled = $locHelper->isLocationsEnabled();
                    if ($locEnabled) {
                        $locationCode = $quoteItem->getEccLocationCode();
                        $newQty = $proHelper->getCorrectOrderQty($product, $qty, $locEnabled, $locationCode, true);
                        if ($newQty['qty'] != $qty) {
                            $qty = $newQty['qty'];
                            $message = $newQty['message'];
                        }
                    }
                }

                if (!$quoteItem) {
                    throw new \Magento\Framework\Exception\LocalizedException($this->__('Quote item is not found.'));
                }
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
                $this->_getCart()->save();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                if (!$quoteItem->getHasError()) {
                    $result['message'] = $this->__('Item was updated successfully.');
                    if ($message) {
                        $result['message'] = $message;
                    }
                } else {
                    $result['notice'] = $quoteItem->getMessage();
                }
                $result['success'] = 1;
            } catch (\Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not save item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    }
