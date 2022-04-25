<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Store;


/**
 * Store Switcher
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Switcher extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        array $data = []
    ) {
        $this->imageFactory = $imageFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->logger = $context->getLogger();
        $this->directoryList = $directoryList;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Class constructor
     */
    public function _construct()
    {
        parent::_construct();
    }

    public function getImageSize()
    {
        return $this->scopeConfig->getValue('Epicor_Comm/brands/brand_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getBrandImageUrl($brandImage)
    {
        //M1 > M2 Translation Begin (Rule p2-5.3)
        //$urlBase = Mage::getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_MEDIA) . 'brandimage/';
        //$urlBase = Mage::getBaseUrl(\Magento\Store\Model\Store::URL_TYPE_MEDIA) . 'brandimage/';
        $urlBase = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'brandimage/';
        //M1 > M2 Translation End

        return $urlBase . $this->_processBrandImage($brandImage);
    }

    public function _processBrandImage($brandImage)
    {
        $size = $this->scopeConfig->getValue('Epicor_Comm/brands/brand_image_size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $brandImageFileName = $size . 'x' . $size . $brandImage;

        //process brand image
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //$basePath = Mage::getBaseDir('media') . DS . 'brandimage' . DS;
        $basePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'brandimage' . DIRECTORY_SEPARATOR;
        //M1 > M2 Translation End

        if (!file_exists($basePath . $brandImageFileName)) {
            try {
                $_image = $this->imageFactory->create($basePath . $brandImage);
                $_image->constrainOnly(true);
                $_image->keepAspectRatio(true);
                $_image->keepFrame(true);
                $_image->keepTransparency(true);
                $_image->resize($size, $size);
                $_image->save($basePath, $brandImageFileName);
            } catch (\Exception $e) {
                $this->logger->debug('--- error saving uploaded brand image ---');
                $this->logger->debug($e);
            }
        }

        return $brandImageFileName;
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }
    //M1 > M2 Translation End

}
