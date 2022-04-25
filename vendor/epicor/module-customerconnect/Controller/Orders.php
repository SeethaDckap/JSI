<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller;


/**
 * Orders controller
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
abstract class Orders extends \Epicor\Customerconnect\Controller\Generic
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    protected $urlDecoder;

    protected $encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->request = $request;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->generic = $generic;
        $this->urlDecoder = $urlDecoder;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
    }
    /**
     * Performs a CUOD request
     * 
     * @return boolean
     */
    protected function _getOrderDetails()
    {
        $orderId = $this->request->getParam('order');
        $order = false;
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        $orderInfo = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($orderId)));
        if (
            count($orderInfo) == 2 &&
            $orderInfo[0] == $erpAccountNumber &&
            !empty($orderInfo[1])
        ) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //$result = $helper->sendOrderRequest($erpAccountNumber, $orderInfo[1], $helper->getLanguageMapping(Mage::app()->getLocale()->getLocaleCode()));
            $result = $helper->sendOrderRequest($erpAccountNumber, $orderInfo[1], $helper->getLanguageMapping($this->_localeResolver->getLocale()));
            //M1 > M2 Translation End

            if ($result['order']) {
                $this->registry->register('customer_connect_order_details', $result['order']);
                $order = true;
            } else {
                $this->messageManager->addErrorMessage(__($result['error']));
            }
        } else {
            $this->messageManager->addErrorMessage(__('ERROR - Invalid Order Number'));
        }

        return $order;
    }

}
