<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Submitconfiguration extends \Epicor\Customerconnect\Controller\Rfqs
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Configurator $commConfiguratorHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $registry,
            $customerconnectHelper,
            $request,
            $customerconnectMessageRequestCrqd,
            $generic,
            $commonAccessHelper,
            $customerconnectMessagingHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor
        );
    }

    public function execute()
    {
        $productHelper = $this->commProductHelper;

        $productId = $this->request->getParam('productid');
        $att = $this->request->getParam('super_attribute');
        $grp = $this->request->getParam('super_group');
        $opt = $this->request->getParam('options');
        
        $qty = $this->request->getParam('qty');
        $currencyCode = $this->getRequest()->getParam('currency_code');

        $helper = $this->commMessagingHelper;

        $product = $this->catalogProductFactory->create();

        $product->load($productId);

        $request = $this->dataObjectFactory->create();
        $request->setData(
                [
                    'product' => $productId,
                    'qty' => $qty,
                    'options' => $opt,
                ]
        );

        if ($product->getTypeId() == 'grouped' && $grp) {
            $request['super_group'] = $grp;
        } else if ($product->getTypeId() == 'grouped' && is_null($grp)) {
            $errorMessage = __('Please specify the quantity of product(s).');
            $response = json_encode(array('error' => $errorMessage));
            $this->getResponse()->setBody($response);
            return;
        }

        if ($product->getTypeId() == 'configurable' && $att) {
            $request['super_attribute'] = $att;
        }


        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product, null);

        $finalProduct = array();
        if(is_array($cartCandidates)){
                foreach ($cartCandidates as $candidate) {
            if ($product->getTypeId() == 'configurable') {
                $finalProduct = $candidate;
            } else if ($product->getTypeId() == 'grouped') {
                $finalProduct[] = $candidate;
            } else {
                $finalProduct = $candidate;
            }
        }
        }


        if (!$finalProduct) {
            $response = json_encode(array('error' => $cartCandidates));
        } else {
            $msq = $this->commMessageRequestMsqFactory->create();
            $msq->setTrigger('RFQ configure');

            if (!empty($currencyCode)) {
                $currencyCode = $helper->getCurrencyMapping($currencyCode, \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
                $msq->addCurrency($currencyCode);
            }

            if (!is_array($finalProduct)) {
                $msq->addProduct($finalProduct, $qty);
            } else {
                foreach ($finalProduct as $fProduct) {
                    $msq->addProduct($fProduct, $grp[$fProduct->getId()]);
                }
            }

            $success = $msq->sendMessage();
            $prodArray = array();

            $customerSession = $this->customerSession;

            $customer = $customerSession->getCustomer();

            if (!is_array($finalProduct)) {
                $this->_priceProduct($finalProduct, $qty, $currencyCode, $success);
                $product = $this->catalogProductFactory->create()->load($finalProduct->getId());
                $finalProduct->setEccUom($product->getEccUom());
                $prodArray[$productId] = $productHelper->getProductInfoArray($finalProduct);
            } else {
                $prodArray['grouped'] = array();
                foreach ($finalProduct as $fProduct) {
                    $this->_priceProduct($fProduct, $grp[$fProduct->getId()], $currencyCode, $success);
                    $product = $this->catalogProductFactory->create()->load($fProduct->getId());
                    $fProduct->setEccUom($product->getEccUom());

                    $prodArray['grouped'][] = $productHelper->getProductInfoArray($fProduct);
                }
            }

            $response = json_encode($prodArray);
        }
        $this->getResponse()->setBody($response);
    }

}
