<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Helper\Product\Image;

/**
 * This class is responsible for syncing image from ERP to magento
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sync extends \Epicor\Comm\Helper\Image\Sync
{
    protected $_syncError = false;
    protected $_imageTypes = array(
        'L' => 'image',
        'T' => 'thumbnail',
        'S' => 'small_image'
    );

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ProcessorFactory
     */
    protected $galleryProcessorFactory;


    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    protected $galleryResourceModel;

    /**
     * @var array
     */
    private $existingMediaGallery = [];

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Magento\Catalog\Model\Product\Gallery\ProcessorFactory $galleryProcessorFactory,
        \Magento\Catalog\Model\Product\Gallery\EntryFactory $mediaGalleryEntryFactory,
        \Magento\Catalog\Model\Product\Gallery\GalleryManagementFactory $mediaGalleryManagementFactory,
        \Magento\Framework\Api\ImageContentFactory $imageContentFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $galleryResourceModel
    )
    {
        $this->commFileHelper = $commFileHelper;
        $this->galleryProcessorFactory = $galleryProcessorFactory;
        $this->mediaGalleryEntryFactory = $mediaGalleryEntryFactory;
        $this->mediaGalleryManagementFactory = $mediaGalleryManagementFactory;
        $this->imageContentFactory = $imageContentFactory;
        $this->galleryResourceModel = $galleryResourceModel;

        parent::__construct($context);
    }

    /**
     * Processes ERP Images for the given product id
     *
     * @param integer $productId
     * @param boolean $force
     *
     * return void
     */
    public function processErpImages($productId, $force = false)
    {

        $product = $this->catalogProductFactory->create()->setStoreId(0)->load($productId);

        /* @var $product Epicor_Comm_Model_Product */
        if ($product->getEccErpImagesProcessed() == 0 || $force) {

            $this->syncErpImages($product, $product->getEccErpImages());

            //M1 > M2 Translation Begin (Rule P2-6.8)
            //if (Mage::app()->isSingleStoreMode() == false) {
            if ((!$this->storeManager->isSingleStoreMode()) && (!$this->storeManager->hasSingleStore())) {
                //M1 > M2 Translation End
                $this->syncStoreErpImages($product, $product->getEccErpImages());
            }
        }
    }

    /**
     * Syncs images for a product & the images provided
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $erpImages
     *
     * @return array
     */
    protected function syncErpImages($product, $erpImages, $update = false)
    {
        $updatedImages = array();
        $this->_syncError = false;
        if (!empty($erpImages)) {

            if ($product->getStoreId() == 0) {
                $product->setData('small_image', 'no_selection');
                $product->setData('thumbnail', 'no_selection');
                $product->setData('image', 'no_selection');
            }

            if (is_string($erpImages)) {
                $erpImages = unserialize($erpImages);
            }

            if (is_array($erpImages)) {
                foreach ($erpImages as $erpImage) {
                    if ($update) {
                        $updatedImage = $this->updateImage($product, $erpImage);

                    } else {
                        $updatedImage = $this->syncImage($product, $erpImage);
                    }

                    if ($updatedImage) {
                        $updatedImages[] = $updatedImage;
                    }
                }
            }

            if ($this->_syncError === false) {
                $product->setEccErpImagesProcessed(1);
            } else {
                $product->setEccErpImagesProcessed(0);
            }

            $product->getResource()->saveAttribute($product, 'thumbnail');
            $product->getResource()->saveAttribute($product, 'small_image');
            $product->getResource()->saveAttribute($product, 'image');

            if ($product->getStoreId() == 0) {
                //M1 > M2 Translation Begin (Rule 25)
                //$product->setEccErpImagesLastProcessed(now());
                $product->setEccErpImagesLastProcessed(date('Y-m-d H:i:s'));
                //M1 > M2 Translation End
                $product->setEccPreviousErpImages(serialize($product->getEccErpImages()));
                $product->setEccErpImages(serialize($updatedImages));

                $product->getResource()->saveAttribute($product, 'ecc_erp_images_last_processed');
                $product->getResource()->saveAttribute($product, 'ecc_previous_erp_images');
                $product->getResource()->saveAttribute($product, 'ecc_erp_images_processed');
                $product->getResource()->saveAttribute($product, 'ecc_erp_images');
            }

        }

        return $updatedImages;
    }

    /**
     * Syncs images for a product for the relevant stores
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $images
     *
     * @return array
     */
    protected function syncStoreErpImages($product, $images)
    {
        if ($images) {
            if (is_string($images)) {
                $images = unserialize($images);
            }
            $storeImages = array();
            if (is_array($images)) {
                foreach ($images as $image) {
                    if (is_array($image['stores']) && isset($image['stores'], $image['store_info']) &&
                        $image['stores'] && $image['store_info']
                    ) {
                        foreach ($image['stores'] as $storeId) {
                            if ($this->isValidStore($storeId) && $image['store_info'][$storeId]) {
                                $storeImages[$storeId][] = array_merge($image, $image['store_info'][$storeId]);
                            }
                        }
                    }
                }
            }


            foreach ($storeImages as $storeId => $images) {
                $storeProduct = $this->catalogProductFactory->create()->setStoreId($storeId)->load($product->getId());
                $this->syncErpImages($storeProduct, $images, true);
            }
        }
    }

    /**
     *
     * @param type $filePath
     * @param type $fileName
     * @param type $sku
     * @param type $types
     *
     * @return \Magento\Catalog\Model\Product\Gallery\Entry
     */
    public function processMediaGalleryEntry($filePath, $fileName, $sku, $types)
    {
        /* @var $entry \Magento\Catalog\Model\Product\Gallery\Entry */
        $entry = $this->mediaGalleryEntryFactory->create();

        $entry->setFile($filePath);
        $entry->setMediaType('image');
        $entry->setDisabled(false);
        $entry->setTypes($types);

        $imageContent = $this->imageContentFactory->create();
        $imageContent
            ->setType(mime_content_type($filePath))
            ->setName($fileName)
            ->setBase64EncodedData(base64_encode(file_get_contents($filePath)));

        $entry->setContent($imageContent);

        $manager = $this->mediaGalleryManagementFactory->create();
        /* @var $manager \Magento\Catalog\Model\Product\Gallery\GalleryManagement */
        $manager->create($sku, $entry);
        $images = $manager->getList($sku);
        return array_pop($images);
    }


    /**
     * Processes a ERP image for the given product, will add, update, or remove the image
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $erpImage
     *
     *  returns an array of image information if image was added / updated
     *  returns false if image removed
     *
     * @return mixed
     */
    protected function syncImage($product, $erpImage)
    {
        // if no stores then remove image

        $hasValidStores = $this->hasValidStores($erpImage);

        if (
            (isset($erpImage['stores']) == false ||
                empty($erpImage['stores']) ||
                $hasValidStores == false) &&
            empty($erpImage['media_filename']) == false
        ) {
            $this->removeImage($product, $erpImage);
            return false;
        }

        if ($hasValidStores == false) {
            return false;
        }

        if (empty($erpImage['filename'])) {
            $erpImage['status'] = 'No image filename associated with this product';
            $this->_syncError = true;
            return $erpImage;
        }
        // if no file then error
        $imagePath = $this->getAssetsFolder();
        $file = $this->getRemoteFile($product, $erpImage, $imagePath);
        if ($file == false) {
            $erpImage['status'] = 'ERP file not found';
            $this->_syncError = true;
            return $erpImage;
        }

        try {
            /* @var $galleryObj \Magento\Catalog\Model\Product\Gallery\Processor */
            $galleryObj = $this->galleryProcessorFactory->create();

            $typeData = $this->getImageTypes($product, $erpImage);
            $types = $typeData['types'];
            $typeBlank = $typeData['blank'];
            $sync = $this->shouldImageBeSynced($product, $erpImage, $file);

            $imageMissing = (
                !$galleryObj || empty($erpImage['media_filename']) ||
                !$galleryObj->getImage($product, $erpImage['media_filename'])
            );


            if ($sync || $imageMissing) {
                $this->removeImage($product, $erpImage);
                //$file = str_replace($this->directoryList->getRoot(), '', $file);
                //$product->addImageToMediaGallery($file, ($typeBlank) ? null : $types, false, false);

                $newImage = $this->processMediaGalleryEntry(
                    $file, $erpImage['filename'], $product->getSku(), ($typeBlank) ? null : $types
                );
                $newImage['label'] = isset($erpImage['description']) ? $erpImage['description'] : '';
                if (!empty($newImage['file'])) {
                    $erpImage['media_filename'] = $newImage['file'];
                    $erpImage['status'] = 1;
                } else {
                    $erpImage['status'] = 'Failed to sync';
                    $this->_syncError = true;
                }
            }

            if (
                empty($types) == false &&
                isset($erpImage['media_filename']) &&
                empty($erpImage['media_filename']) == false
            ) {
                foreach ($types as $type) {
                    $product->setData($type, $erpImage['media_filename']);
                }
            }

            $erpImage['attachment_status'] = '';
        } catch (\Exception $e) {
            $erpImage['status'] = $e->getMessage();
            $this->_syncError = true;
        }


        return $erpImage;
    }

    /**
     * Processes a ERP image for the given product, will add, update, or remove the image
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $erpImage
     *
     *  returns an array of image information if image was added / updated
     *  returns false if image removed
     *
     * @return mixed
     */
    protected function updateImage($product, $erpImage)
    {
        try {
            $typeData = $this->getImageTypes($product, $erpImage);
            $types = $typeData['types'];
            if(is_array($types)) {
                foreach ($types as $type) {
                    if (isset($erpImage['media_filename']) && $erpImage['media_filename'] != '') {
                        $product->setData($type, $erpImage['media_filename']);
                    }
                }
            }
        } catch (\Exception $e) {
            $erpImage['status'] = $e->getMessage();
            $this->_syncError = true;
        }


        return $erpImage;
    }

    /**
     * Works out the image types to be done for
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $erpImage
     * @return array
     */
    protected function getImageTypes($product, $erpImage)
    {
        $types = array();
        $typeBlank = true;

        //M1 > M2 Translation Begin (Rule P2-6.8)
        /*if (
            empty($erpImage['types']) == false &&
            (($product->getStoreId() == 0 && Mage::app()->isSingleStoreMode()) ||
            ($product->getStoreId() > 0 && Mage::app()->isSingleStoreMode() == false))
        ) {*/
        if (
            empty($erpImage['types']) == false &&
            (($product->getStoreId() == 0 && $this->storeManager->isSingleStoreMode()) ||
                ($product->getStoreId() > 0 && $this->storeManager->isSingleStoreMode() == false))
        ) {
            //M1 > M2 Translation End
            // if only a G is passed, then the types we pass to
            // addImageToMediaGallery needs to be blank
            if (isset($erpImage['types']) && is_array($erpImage['types'])) {
                foreach ($erpImage['types'] as $type) {
                    $typeBlank = ($type != 'G') ? false : $typeBlank;
                    if (!empty($this->_imageTypes[$type])) {
                        $types[] = $this->_imageTypes[$type];
                    }
                }
            }
        } else {
            /* added code et image type when types are given in ERP data */
            if (isset($erpImage['types']) && is_array($erpImage['types'])) {
                foreach ($erpImage['types'] as $type) {
                    if (!empty($this->_imageTypes[$type])) {
                        $types[] = $this->_imageTypes[$type];
                    }
                }
            }
            if (count($types) > 0) {
                $typeBlank = false;
            }
        }

        return array(
            'types' => $types,
            'blank' => $typeBlank
        );
    }

    /**
     * Works out if an image needs to be synced based on time
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $erpImage
     * @param string $file
     *
     * @return boolean
     */
    protected function shouldImageBeSynced($product, $erpImage, $file)
    {
        $sync = true;
        if ($erpImage['status'] == 1 && $product->getEccErpImagesLastProcessed()) {
            $lastSync = strtotime($product->getEccErpImagesLastProcessed());
            $lastMod = filemtime($file);
            if ($lastMod < $lastSync) {
                $sync = false;
            }
        }

        return $sync;
    }

    /**
     * Removes the provided image from the product
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $image
     *
     * @return void
     */
    protected function removeImage($product, $image)
    {
        if (isset($image['media_filename'])) {
            if (empty($this->existingMediaGallery)) {
                $this->existingMediaGallery = $product->getMediaGalleryImages();
            }
            foreach ($this->existingMediaGallery as $existingEntry) {
                if ($existingEntry->getFile() == $image['media_filename']) {
                    $this->galleryResourceModel->deleteGallery($existingEntry->getValueId());
                    $this->galleryProcessorFactory->create()->removeImage($product, $existingEntry->getFile());
                }
            }
        }
        /* below code not working in Magento 2.0 */
        //$gallery->getBackend()->removeImage($product, $image['media_filename']);
        $storeIds = $this->getAllStoreIds();

        $types = array(
            'image',
            'small_image',
            'thumbnail',
        );

        $filename = isset($image['media_filename']) ? $image['media_filename'] : '';

        foreach ($storeIds as $storeId) {
            $storeProduct = $this->catalogProductFactory->create()->setStoreId($storeId)->load($product->getId());
            /* @var $storeProduct Epicor_Comm_Model_Product */
            foreach ($types as $type) {
                if ($storeProduct->getData($type) == $filename) {
                    $storeProduct->setData($type, 'no_selection');
                    $storeProduct->getResource()->saveAttribute($storeProduct, $type);
                }
            }
        }
    }

    /**
     * gets all store ids
     *
     * @return array
     */
    protected function getAllStoreIds()
    {
        $storeIds = array();
        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $storeIds[] = $store->getId();
                }
            }
        }

        return $storeIds;
    }

    /**
     * Retrieve image from remote location either from given URL or create file from base64 content
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $image
     *
     * @return void
     */
    public function getRemoteFile($product, $attachment, $path, $endOnNoFile = false, $allowForce = true)
    {
        $filename = @$attachment['filename'] ?: false;
        $file = $this->fileExists($path, $filename, false);
        if ($endOnNoFile && $file == false) {
            return false;
        }
        $attachmentStatus = isset($attachment['attachment_status']) ? $attachment['attachment_status'] : '';
        $forceRefresh = ($attachmentStatus == 'R' && $allowForce) ? true : false;
        $erpFile = (isset($attachment['erp_file_id']) || isset($attachment['url']));
        $fileData = '';
        if ($forceRefresh && $erpFile) {
            if (isset($attachment['media_filename']) && $attachment['media_filename']) {
                $this->removeImage($product, $attachment);
            }
            $fileHelper = $this->commFileHelper;
            $fileInfo = $fileHelper->requestFile(
                @$attachment['web_file_id'], @$attachment['erp_file_id'], $filename, true
            );
            $fileData = isset($fileInfo['content']) ? $fileInfo['content'] : false;
        } else if (!$file && $erpFile) {
            $fileHelper = $this->commFileHelper;
            $fileData = $fileHelper->getRemoteContent(
                @$attachment['web_file_id'], @$attachment['erp_file_id'], $filename, @$attachment['url']
            );
        }
        if (!empty($fileData)) {
            file_put_contents($path . $filename, $fileData);
        }
        return $this->fileExists($path, $filename, false);
    }

    /**
     * Checks whether file exist or not at remote location
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $image
     *
     * @return void
     */
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
            if (is_array($fileArray)) {
                foreach ($fileArray as $file) {
                    if (strtolower($file) == $fileNameLowerCase) {
                        return $file;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns the assets folder where images are copied
     *
     * @return string
     */
    public function getAssetsFolder()
    {
        return $this->getMediaFolder() . str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $this->scopeConfig->getValue(
                    'Epicor_Comm/assets/product_image',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
    }

}
