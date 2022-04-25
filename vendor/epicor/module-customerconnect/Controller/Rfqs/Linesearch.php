<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Controller\Rfqs;

class Linesearch extends \Epicor\Customerconnect\Controller\Rfqs
{

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchHelper;
    
    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;    

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
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->queryFactory = $queryFactory;
        $this->resultJsonFactory = $resultJsonFactory;
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
        foreach ($this->getRequest()->getParams() as $key => $value) {
            if (substr($key, 0, 4) == 'amp;')
                $this->getRequest()->setParam(substr($key, 4), $value);
        }

        $q = $this->getRequest()->getParam('q', '');
        $instock = $this->getRequest()->getParam('instock', '');
        $this->registry->register('search-query', $q);

        if (!empty($q)) {

//        Mage::register('search-sku', $sku);
            $this->registry->register('search-instock', $instock != '' ? true : false);

            /** @var \Magento\Search\Model\Query $query */
            $query = $this->queryFactory->get();

            $query->setStoreId($this->storeManager->getStore()->getId());
            if ($this->catalogSearchHelper->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            } else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity() + 1);
                } else {
                    $query->setPopularity(1);
                }

                $query->prepare();
            }

            $this->catalogSearchHelper->checkNotes();

            if (!empty($q) && !$this->catalogSearchHelper->isMinQueryLength()) {
                $query->save();
            }
        }


        return $this->resultPageFactory->create();
    }

}
