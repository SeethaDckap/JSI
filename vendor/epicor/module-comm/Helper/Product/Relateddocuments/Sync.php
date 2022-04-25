<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Helper\Product\Relateddocuments;

/**
 * This class is responsible for syncing Related documents from ERP to magento
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sync extends \Epicor\Comm\Helper\Image\Sync
{

    protected $_syncError = false;

    /**
     * @var \Epicor\Comm\Helper\File
     */
    protected $commFileHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ProcessorFactory
     */
    protected $galleryProcessorFactory;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Helper\File $commFileHelper,
        \Magento\Catalog\Model\Product\Gallery\ProcessorFactory $galleryProcessorFactory,
        \Magento\Catalog\Model\Product\Gallery\EntryFactory $mediaGalleryEntryFactory,
        \Magento\Catalog\Model\Product\Gallery\GalleryManagementFactory $mediaGalleryManagementFactory,
        \Magento\Framework\Api\ImageContentFactory $imageContentFactory
    )
    {
        $this->commFileHelper = $commFileHelper;
        $this->galleryProcessorFactory = $galleryProcessorFactory;
        $this->mediaGalleryEntryFactory = $mediaGalleryEntryFactory;
        $this->mediaGalleryManagementFactory = $mediaGalleryManagementFactory;
        $this->imageContentFactory = $imageContentFactory;

        parent::__construct($context);
    }

    /**
     * Processes Related Documents for the given product id
     *
     * @param integer $productId
     * @param boolean $force
     *
     * return void
     */
    public function processRelatedDocuments($productId, $force = false)
    {

        $product = $this->catalogProductFactory->create()->setStoreId(0)->load($productId);
        /* @var $product \Epicor\Comm\Model\Product */

        if ($product->getEccRelatedDocumentsSynced() == 0 || $force) {
            $productRelatedDocuments = $product->getEccRelatedDocuments();
            $this->syncRelatedDocs($product, $productRelatedDocuments);
            if ((!$this->storeManager->isSingleStoreMode())
                && (!$this->storeManager->hasSingleStore())
            ) {
                $this->syncStoreRelatedDocs($product);
            }
        }
    }

    /**
     * Syncs Related Docs for a product
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $relatedDocs
     *
     * @return array
     */
    protected function syncRelatedDocs($product, $relatedDocs, $update = false)
    {
        $relatedDocuments = [];
        $this->_syncError = false;

        if (!empty($relatedDocs) && !is_null($relatedDocs)) {
            if(!is_array($relatedDocs)){
                $relatedDocs = unserialize($relatedDocs);
            }
            foreach ($relatedDocs as $relatedDoc) {
                if ($relatedDoc['erp_file_id'] && (!isset($relatedDoc['url']) || $relatedDoc['url'] == '')) {
                   $this->syncDoc($product, $relatedDoc);
                }
                if ($this->_syncError === false) {
                    $relatedDoc['sync_required'] = "N";
                }
                $relatedDocuments[] = $relatedDoc;
            }
            sort($relatedDocuments);
            if ($this->_syncError === false) {
                $product->setEccRelatedDocumentsSynced(1);
                $value = serialize($relatedDocuments);
                $product->setEccRelatedDocuments($value);
                $product->getResource()->saveAttribute($product, 'ecc_related_documents');
            } else {
                $product->setEccRelatedDocumentsSynced(0);
                $value = serialize($relatedDocuments);
                $product->setEccRelatedDocuments($value);
                $product->getResource()->saveAttribute($product, 'ecc_related_documents');
            }

            if ($product->getStoreId() == 0) {
                $product->getResource()->saveAttribute($product, 'ecc_related_documents_synced');
            }
        }

        return $relatedDocs;
    }

    /**
     * Syncs Related Docs for a product for the relevant stores
     *
     * @param \Epicor\Comm\Model\Product $product
     * @return array
     */
    protected function syncStoreRelatedDocs($product)
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeProduct = $this->catalogProductFactory->create()
                ->setStoreId($store->getStoreId())->load($product->getId());
            $storeValue = $storeProduct->getExistsStoreValueFlag('ecc_related_documents');
            if ($storeProduct->getId()
                && $storeValue
            ) {
                $relatedDocs = $storeProduct->getEccRelatedDocuments();
                if ($relatedDocs) {
                    if (is_string($relatedDocs)) {
                        $relatedDocs = unserialize($relatedDocs);
                    }
                    $this->syncRelatedDocs($storeProduct, $relatedDocs, true);
                }
            }
        }
        return;
    }


    /**
     * Adds Related Doc for the given product
     *
     * @param Epicor_Comm_Model_Product $product
     * @param array $relatedDoc
     *
     * @return void
     */
    protected function syncDoc($product, $relatedDoc)
    {

        $docPath = $this->getAssetsFolder();
        $file = $this->getRemoteFile($product, $relatedDoc, $docPath);
        if ($file == false) {
            $this->_syncError = true;
        }
        return;
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
            foreach ($fileArray as $file) {
                if (strtolower($file) == $fileNameLowerCase) {
                    return $file;
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
                    'Epicor_Comm/assets/product_related',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
    }

}
