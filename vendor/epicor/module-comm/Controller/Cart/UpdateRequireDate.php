<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;

class UpdateRequireDate extends \Epicor\Comm\Controller\Cart
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Epicor\Comm\Helper\Cart\SendbsvFactory
     */
    protected $sendBsvHelperFactory;

    /**
     * @var CustomerCart
     */
    protected $checkoutCart;

    /**
     * UpdateRequireDate constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $checkoutCart
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Epicor\Comm\Helper\Locations $commLocationsHelper
     * @param \Magento\Catalog\Model\ProductFactory $catalogProductFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistResourceModelItemCollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $checkoutCart,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistResourceModelItemCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Epicor\Comm\Helper\Cart\SendbsvFactory $sendBsvHelperFactory
    ) {
        $this->logger = $logger;
        $this->checkoutCart = $checkoutCart;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sendBsvHelperFactory = $sendBsvHelperFactory;

        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $checkoutCart,
            $commProductHelper,
            $commLocationsHelper,
            $catalogProductFactory,
            $customerSession,
            $wishlistResourceModelItemCollectionFactory
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $date = $this->getRequest()->getParam('ecc_required_date');
        $return = array("success" => false,"error_message"=>"");
        $requireDate = "";
        if($this->getRequest()->isPost()) {
            if($date){
                $date = strtotime($date);
                $requireDate = date('Y-m-d', $date);
            }
            $quote = $this->checkoutCart->getQuote();
            $quote->setEccRequiredDate($requireDate);
            $quote->setEccIsDdaDate(true);
            $helper = $this->sendBsvHelperFactory->create();
            try {
                $helper->sendCartBsv($quote, true);
                $return["success"] = true;
            } catch (\Exception $e) {
                //$this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->critical($e);
                $return["success"] = false;
                $return["error"] = $e->getMessage();
            }
            $quote->setEccRequiredDate($requireDate);
            try {
                $quote->save();
            } catch (\Exception $e) {
                //$this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->critical($e);
            }

        } else {
            $return["error_message"] = __("Request is not accepted.");
        }

        return $result->setData($return);
    }
}
