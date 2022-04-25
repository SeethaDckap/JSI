<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


class File extends \Epicor\Common\Helper\File
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\FsubFactory
     */
    protected $commMessageRequestFsubFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\FreqFactory
     */
    protected $commMessageRequestFreqFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Epicor\Common\Model\Mail\TransportBuilder
     */
    protected $_transportBuilder;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\Common\Model\FileFactory $commonFileFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\ReturnModel\Attachment\CollectionFactory $commResourceCustomerReturnModelAttachmentCollectionFactory,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Epicor\Comm\Model\Message\Request\FsubFactory $commMessageRequestFsubFactory,
        \Epicor\Comm\Model\Message\Request\FreqFactory $commMessageRequestFreqFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Epicor\Common\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ) {
        $this->commMessageRequestFsubFactory = $commMessageRequestFsubFactory;
        $this->commMessageRequestFreqFactory = $commMessageRequestFreqFactory;
        parent::__construct(
            $context,
            $commonFileFactory,
            $commCustomerErpaccountFactory,
            $commResourceCustomerReturnModelAttachmentCollectionFactory,
            $commCustomerReturnModelFactory,
            $inlineTranslation,
            $transportBuilder,
            $driverFile
        );
    }
    public function submitFile($file, $action = 'A')
    {
        $fsub = $this->commMessageRequestFsubFactory->create();

        if ($fsub->isActive()) {
            $fsub->setFile($file);
            $fsub->setAction($action);
            $fsub->sendMessage();
        }
    }

    public function requestFile($webFileId, $erpFileId, $filename, $setDefaultStore = false)
    {
        $freq = $this->commMessageRequestFreqFactory->create();

        $fileData = array();

        if ($freq->isActive()) {
            if ($setDefaultStore) {
                //M1 > M2 Translation Begin (Rule p2-6.5)
                //$defStoreId = Mage::app()->getDefaultStoreView()->getStoreId();
                $defStoreId = $this->storeManager->getDefaultStoreView()->getId();
                //M1 > M2 Translation End
                $freq->setStoreId($defStoreId);
            }
            $freq->setWebFileId($webFileId);
            $freq->setErpFileId($erpFileId);
            $freq->setFilename($filename);
            if ($freq->sendMessage()) {
                $fileData = $freq->getFileData();
            }
        }

        return $fileData;
    }

    public function fileExists($path, $file, $caseSensitive = true)
    {
        $fileName = $path . $file;
        if (file_exists($fileName)) {
            return $fileName;
        }

        if (!$caseSensitive) {
            // Handle case insensitive requests
            $fileArray = glob($path . '*', GLOB_NOSORT);
            $fileNameLowerCase = strtolower($fileName);
            foreach ($fileArray as $file) {
                if (strtolower($file) == $fileNameLowerCase) {
                    return $file;
                }
            }
        }
        return false;
    }

}
