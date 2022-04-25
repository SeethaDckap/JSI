<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurator;

use Epicor\Customerconnect\Controller\Rfqs;

/**
 * Class Ewacomplete
 * @package Epicor\Comm\Controller\Configurator
 */
class Rfqewasubmit extends Rfqs
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * Rfqewacomplete constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Customerconnect\Helper\Data $customerconnectHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Epicor\Customerconnect\Model\Message\Request\Crqd $customerconnectMessageRequestCrqd
     * @param \Magento\Framework\Session\Generic $generic
     * @param \Epicor\Common\Helper\Access $commonAccessHelper
     * @param \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper
     * @param \Epicor\Comm\Helper\Messaging $commMessagingHelper
     * @param \Epicor\Comm\Helper\Configurator $commConfiguratorHelper
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Magento\Catalog\Model\ProductFactory $catalogProductFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Epicor\Comm\Model\Message\Request\CdmFactory $commMessageRequestCdmFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->formKey = $formKey;
        $this->request = $request;
        $this->registry = $registry;
        $this->request->setParam('form_key', $this->formKey->getFormKey());
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

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->registry->register('line_edit', true);
        $this->registry->register('line_no',$this->urlDecoder->decode($this->request->getParam('lineNumber')));
        $this->_ewaProcess();
    }

}