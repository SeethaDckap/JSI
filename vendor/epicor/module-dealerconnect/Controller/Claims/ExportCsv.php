<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Claims;

use Magento\Framework\App\Filesystem\DirectoryList;
class ExportCsv extends \Epicor\Dealerconnect\Controller\Claims
{

    private $_fileFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Dealerconnect\Model\Message\Request\Dcld $dealerconnectMessageRequestDcld,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Access $commonAccessHelper,
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
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Epicor\Dealerconnect\Model\Message\Request\Deid $dcMessageRequestDeid
    )
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $registry,
            $dealerconnectHelper,
            $request,
            $dealerconnectMessageRequestDcld,
            $generic,
            $commonAccessHelper,
            $commMessagingHelper,
            $commConfiguratorHelper,
            $commProductHelper,
            $catalogProductFactory,
            $storeManager,
            $commMessageRequestCdmFactory,
            $scopeConfig,
            $commonXmlvarienFactory,
            $urlDecoder,
            $encryptor,
            $dcMessageRequestDeid
        );
    }
    /**
     * Export Claim grid to CSV format
     */
    public function execute()
    {
        $baseUrl = $this->dealerconnectHelper->urlWithoutHttp();
        $fileName = $baseUrl . '_claim.csv';
        $exportBlock = $this->_view->getLayout()->createBlock('Epicor\Dealerconnect\Block\Claims\Listing\Grid', '', [
            'data' => [
                'is_export' => true
            ]
        ]);

        return $this->_fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }

}
