<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Catalog\Category\Image;


/**
 * This class is responsible for syncing category image from ERP to magento
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sync extends \Epicor\Comm\Helper\Image\Sync
{

    private $_syncError = false;

    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    protected $indexerIndexer;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;
    
    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Magento\Indexer\Model\Indexer $indexerIndexer,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Epicor\Comm\Helper\File $commFileHelper
    ) {
        $this->indexerIndexer = $indexerIndexer;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->commFileHelper = $commFileHelper;
        parent::__construct($context);
    }
    /**
     * main function responsible for syncing image and setting store level media gallery
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param boolean $force
     * @return void
     */
    public function processErpImages(\Magento\Catalog\Model\Category $category, $force = false)
    {  
        if ($category->getEccErpImagesProcessed() =="0" || $force) { 
            $erpImages = $category->getEccErpImages();
            $erpImages = is_array($erpImages) ? $erpImages : unserialize($erpImages); 
            $this->syncBaseErpImages($category, $erpImages);
            //$this->syncStoreErpImages($category, $erpImages);
            // disabled for now Ashwani Arya
            /*
            $this->indexCategory($category);
            */
        } 
    }

    /**
     * Indexes the Category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    private function indexCategory($category)
    {
        $this->indexerIndexer->processEntityAction(
            $category, \Magento\Catalog\Model\Category::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
    }

    /**
     * Processes ERP images for the given category ID
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $erpImages
     * @return void
     */
    private function syncBaseErpImages(\Magento\Catalog\Model\Category $category, $erpImages)
    {
        $this->_syncError = false;

        $updatedImages = array();
        if (!empty($erpImages)) {
            foreach ($erpImages as $erpImage) {
                $updatedImages[] = $this->processImage($category, $erpImage);
            }
            $category->setEccErpImagesProcessed($this->_syncError === false ? 1 : 0);
            //M1 > M2 Translation Begin (Rule 25)
            //$category->setEccErpImagesLastProcessed(now());
            $category->setEccErpImagesLastProcessed(date('Y-m-d H:i:s'));
            //M1 > M2 Translation End
            $category->setEccErpImages(serialize($updatedImages));
            $category->setEccPreviousErpImages(serialize($category->getEccErpImages()));
            $category->getResource()->saveAttribute($category, 'ecc_erp_images_last_processed');
            $category->getResource()->saveAttribute($category, 'ecc_erp_images');
            $category->getResource()->saveAttribute($category, 'ecc_erp_images_processed');
        }

        return $updatedImages;
    }

    /**
     * set store level values for media gallery
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $images
     * @return void
     */
    private function syncStoreErpImages(\Magento\Catalog\Model\Category $category, $images)
    {
        if ($images) {
            if (is_string($images)) {
                $images = unserialize($images);
            }
            $storeImages = array();
            foreach ($images as $image) {
                if (
                    isset($image['stores']) && isset($image['store_info']) && $image['stores'] && $image['store_info']
                ) {
                    foreach ($image['stores'] as $storeId) {
                        if ($this->isValidStore($storeId) && $image['store_info'][$storeId]) {
                            $storeImages[$storeId][] = array_merge($image, $image['store_info'][$storeId]);
                        }
                    }
                }
            }

            foreach ($storeImages as $storeId => $storeImages) {
                $storeCategory = $this->catalogCategoryFactory->create()->setStoreId($storeId)->load($category->getId());
                foreach ($storeImages as $image) {
                    if (isset($image['filename']) && $image['filename']) {
                        $storeCategory->setImage($image['filename']);
                        $storeCategory->getResource()->saveAttribute($category, 'image');
                    }
                }
            }
        }
    }

    /**
     * Processes a ERP image for the given category, will add, update, or remove the image
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $erpImage
     *
     *  returns an array of image information if image was added / updated
     *  returns false if image removed
     *
     * @return mixed
     */
    private function processImage(\Magento\Catalog\Model\Category $category, $erpImage)
    {
        $hasValidStores = $this->hasValidStores($erpImage);
        // if no stores then remove image
        if (
            isset($erpImage['stores']) == false ||
            empty($erpImage['stores']) ||
            $hasValidStores == false
        ) {
            return false;
        }

        // if no file then error
        $path = $this->getAssetsFolder();
        $file = $this->getRemoteFile($erpImage, $path);
        if ($file == false) {
            $erpImage['status'] = 'ERP file not found';
            $this->_syncError = true;
            return $erpImage;
        }
        
        try { 
            $exploded_array = explode(DIRECTORY_SEPARATOR, $file);
            $filename = array_pop($exploded_array);
            $sync = true;
            if ($erpImage['status'] == 1 && $category->getEccErpImagesLastProcessed()) {
                $lastSync = strtotime($category->getEccErpImagesLastProcessed());
                $lastMod = filemtime($file);
                if ($lastMod < $lastSync) {
                    $sync = false;
                }
            }

            if ($sync) {
                if ($copied = $this->copyImage($filename)) {
                    $category->setImage($filename);
                    $category->getResource()->saveAttribute($category, 'image');
                    $erpImage['media_filename'] = $filename;
                    $erpImage['status'] = 1;
                } else {
                    $erpImage['status'] = 'Failed to sync';
                }
            }
        } catch (Exception $ex) {
            $erpImage['status'] = $ex->getMessage();
        }

        return $erpImage;
    }

    /**
     * Retrives true if image stored in assets folder exists and was coppied correctly
     *
     * @param string $image
     *
     * @return bool
     */
    public function copyImage($image)
    {
        $fileHelper = $this->commFileHelper;
        /* @var $fileHelper Epicor_Comm_Helper_File */

        $copied = false;

        $path = $this->getAssetsFolder();
        if ($fileName = $fileHelper->fileExists($path, $image, false)) {
            $mediaFilePath = $this->getCategoryMediaFolder() . DIRECTORY_SEPARATOR . $image;
            //cho $mediaFilePath; die;
            $copied = @copy($fileName, $mediaFilePath);
        } 

        return $copied;
    }

    /**
     * Retrieve image from remote location either from given URL or create file from base64 content
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $image
     *
     * @return void
     */
    public function getRemoteFile($attachment, $path, $endOnNoFile = false, $allowForce = true)
    {
        $fileHelper = $this->commFileHelper;
        /* @var $fileHelper Epicor_Comm_Helper_File */

        $filename = @$attachment['filename'] ?: false;
        $file = $fileHelper->fileExists($path, $filename, false);
        if ($endOnNoFile && $file == false) {
            return false;
        }

        $attachmentStatus = isset($attachment['attachment_status']) ? $attachment['attachment_status'] : '';
        $forceRefresh = ($attachmentStatus == 'R' && $allowForce) ? true : false;
        $erpFile = (isset($attachment['erp_file_id']) || isset($attachment['url']));
        $fileData = '';
        if ($forceRefresh && $erpFile) {
            $fileInfo = $fileHelper->requestFile(@$attachment['web_file_id'], @$attachment['erp_file_id'], $filename, true);
            $fileData = isset($fileInfo['content']) ? $fileInfo['content'] : false;
        } else if (!$file && $erpFile) {
            $fileData = $fileHelper->getRemoteContent(@$attachment['web_file_id'], @$attachment['erp_file_id'], $filename, @$attachment['url']);
        }
        if (!empty($fileData)) {
            file_put_contents($path . $filename, $fileData);
        }
        return $fileHelper->fileExists($path, $filename, false);
    }
    
    /**
     * Returns the media folder where images are copied
     *
     * @return string
     */
    public function getCategoryMediaFolder()
    {
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //return Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category';
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category';
        //M1 > M2 Translation End
    }
    
    
    /**
     * Returns the assets folder where ftp images are stored
     *
     * @return string
     */
    public function getAssetsFolder()
    {
        //M1 > M2 Translation Begin (Rule p2-5.5)
        //return Mage::getBaseDir() . DS . str_replace('/', DS, $this->scopeConfig->getValue('Epicor_Comm/assets/category_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        return $this->getMediaFolder() . str_replace('/', DIRECTORY_SEPARATOR, $this->scopeConfig->getValue('Epicor_Comm/assets/category_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        //M1 > M2 Translation End
    }
}
