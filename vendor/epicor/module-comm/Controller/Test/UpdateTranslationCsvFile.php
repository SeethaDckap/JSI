<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Test;

class UpdateTranslationCsvFile extends \Epicor\Comm\Controller\Test
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\FlexiTheme\Model\ResourceModel\Translation\Updates\CollectionFactory
     */
    protected $flexiThemeResourceTranslationUpdatesCollectionFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        //\Epicor\FlexiTheme\Model\ResourceModel\Translation\Updates\CollectionFactory $flexiThemeResourceTranslationUpdatesCollectionFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\CacheInterface $cacheManager)
    {
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->eventManager = $context->getEventManager();
        $this->customerSession = $customerSession;
        //$this->flexiThemeResourceTranslationUpdatesCollectionFactory = $flexiThemeResourceTranslationUpdatesCollectionFactory;
        $this->directoryList = $directoryList;
        parent::__construct(
            $context,
            $resourceConfig,
            $moduleReader,
            $cacheManager);
    }

    public function execute()
    {      // languageId and code will only be populated from singleTranslate 
        $languageId = $this->getRequest()->getParam('id');
        $languageCode = $this->getRequest()->getParam('code');

        $updatesCollection = $this->flexiThemeResourceTranslationUpdatesCollectionFactory->create();
        $updatesCollection->getSelect()->joinLeft(array('td' => 'epicor_flexitheme_translation_data'), 'main_table.phrase_id = td.id', array('td.translation_string'));
        $languageCode = ($languageCode) ? $languageCode : $this->getRequest()->getParam('languageCode');
        $languageId = ($languageId) ? $languageId : $this->getRequest()->getParam('id');
        $updatesCollection->addFieldToFilter('language_id', array('eq' => $languageId));
        $updates = $updatesCollection->getData();

        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$path = Mage::getBaseDir('app');     // get base url
        $path = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::APP);     // get base url
        //M1 > M2 Translation End
        $localePath = $path . '/' . "design" . '/' . "frontend" . '/' . "base" . '/' . "default" . '/' . "locale" . '/' . $languageCode;

        if (!file_exists($localePath)) {    // check if locale directory exists and create it if not
            mkdir($localePath, 0777, true);
        }
        $file = $localePath . '/' . "translate.csv";
        $tempFile = $localePath . '/' . "translate" . date('YmdHis') . ".csv";
        if (file_exists($file)) {
            rename($file, $tempFile);
        }
        $fileContents = "";

        foreach ($updates as $update) {
            $fileContents .= "\"" . str_replace('"', '""', $update['translation_string']) . "\",\"" . str_replace('"', '""', $update['translated_phrase']) . "\"\n";
        }
        if (file_put_contents($file, $fileContents)) {                    // write new translation file 
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        };
        echo 'FILE UPDATED';
    }

}
