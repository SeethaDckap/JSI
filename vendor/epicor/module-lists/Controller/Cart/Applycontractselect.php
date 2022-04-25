<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Cart;

class Applycontractselect extends \Epicor\Lists\Controller\Cart
{

    /**
     * @var \Magento\Framework\Url\Decoder
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    public function __construct(
                    \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
             \Magento\Framework\Url\Decoder $urlDecoder    ) {
        $this->commHelper = $commHelper;
        $this->checkoutCart = $checkoutCart;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->urlDecoder = $urlDecoder;
                parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Contract Select Page
     *
     * @return void
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('itemid');
        $contractId = $this->getRequest()->getParam('contract');
        $urlReturn = $this->getRequest()->getParam('return_url');

//        $helper = $this->commHelper;
//        /* @var $helper Epicor_Comm_Helper_Data */

        $cart = $this->checkoutCart;
        /* @var $cart Mage_Checkout_Model_Cart */

        $cartItem = $cart->getQuote()->getItemById($itemId);
        /* @var $cartItem \Mage\Sales\Model\Quote\Item */

        $save = false;
        if (empty($contractId)) {
            $cartItem->setEccContractCode(null);
            $cartItem->setHasDataChanges(true);
            $save = true;
        } else {
            $contract = $this->listsListModelFactory->create()->load($contractId);
            /* @var $contract Epicor_Lists_Model_ListModel */
            if ($cartItem->getEccContractCode() != $contract->getErpCode()) {
                $save = true;
                $cartItem->setEccContractCode($contract->getErpCode());
            }
        }

        if ($save) {
            $cart->save();
        }
        //echo $cartItem->getEccContractCode();die('hi');
        //M1 > M2 Translation Begin (Rule p2-4)
        //$urlReturn = $helper->urlDecode(base64_decode($urlReturn)) ?: Mage::getUrl('checkout/cart');
        $urlReturn = $this->urlDecoder->decode(base64_decode($urlReturn)) ?: $this->_url->getUrl('checkout/cart');
        //M1 > M2 Translation End
        //M1 > M2 Translation Begin (Rule p2-3)
        /*Mage::app()->getResponse()->setRedirect($urlReturn);
        die(Mage::app()->getResponse());*/
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($urlReturn);
        return $resultRedirect;
        //M1 > M2 Translation End
    }

}
