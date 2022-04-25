<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response STT - Upload Text Record
 * 
 * Send up extra textual information about the specified, previously uploaded, product
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Stt extends \Epicor\Comm\Model\Message\Upload
{

    private $_languageStores;
    private $_languageData;
    protected $_maxDeadlockRetriesDefault = 5;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ProductsFactory
     */
    protected $commErpMappingProductsFactory;

    /**
     * @var array
     */
    protected $mappedProductSkus = [];

    /**
     * @var array
     */
    protected $mappedProductUoms = [];

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->commProductHelper = $context->getCommProductHelper();
        $this->commErpMappingProductsFactory = $commErpMappingProductsFactory->create();
        $productsMapping = $this->commErpMappingProductsFactory->getCollection()
            ->addFieldToSelect(['product_sku', 'product_uom'])
            ->getData();
        $uomSeparator = $this->commProductHelper->create()->getUOMSeparator();
        $this->mappedProductSkus = array_column($productsMapping, 'product_sku');
        foreach ($productsMapping as $productUoms) {
            $this->mappedProductUoms[] = $productUoms['product_sku'] . $uomSeparator . $productUoms['product_uom'];
        }
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/stt_mapping/');
        $this->setMessageType('STT');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_PRODUCT);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_product', true, true);

    }


    public function resetProcessFlags()
    {
        parent::resetProcessFlags();
        $this->_languageStores = null;
        $this->_languageData = null;
    }

    /**
     * Process a request
     *
     * @param array $requestData
     * @return 
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest()->getProduct();

        $this->_loadStores();
        $this->_loadStoreLanguages();
        $this->_processLanguageData();

        $productCode = $this->getVarienData('product_code');
        $this->setMessageSubject($productCode);

        if ($productCode) {
            $productId = $this->catalogProductFactory->create()->getIdBySku($productCode);
            $product = $this->catalogProductFactory->create()->load($productId);

            if ($product->isObjectNew()) {
                throw new \Exception(
                $this->getErrorDescription(self::STATUS_PRODUCT_NOT_ON_FILE, $productCode), self::STATUS_PRODUCT_NOT_ON_FILE
                );
            }

            $deleteFlag = $this->getVarienDataFlag('product_delete');

            foreach ($this->_languageData as $language) {
                $code = $this->getVarienData('language_code', $language);
                $stores = isset($this->_languageStores[$code]) ? $this->_languageStores[$code] : array();
                if(count($product->getStoreIds()) == 1){
                     if ($deleteFlag) {
                            $this->deleteProduct($product->getId(), null);
                        } else {
                            $this->updateProduct($product->getId(), $language, null);
                        }
                }else {
                    foreach ($stores as $storeId) {
                        if ($deleteFlag) {
                            $this->deleteProduct($product->getId(), $storeId);
                        } else {
                            $this->updateProduct($product->getId(), $language, $storeId);
                        }
                    }
                }
            }
        } else {
            throw new \Exception('Product Code not defined in message', self::STATUS_GENERAL_ERROR);
        }
    }

    /**
     * Processes the stores and sorts them into an array by language code
     */
    private function _loadStoreLanguages()
    {

        $this->_languageStores = array();

        foreach ($this->_stores as $store) {
            $storeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

            // only add the language if we don't already have it
            if (!isset($this->_languageStores[$storeCode]) || !in_array($store->getId(), $this->_languageStores[$storeCode])) {
                $this->_languageStores[$storeCode][] = $store->getId();
            }
        }
    }

    /**
     * Processes the language group into an array of data
     */
    private function _processLanguageData()
    {
        $languages = $this->getVarienDataArray('languages');
        $this->_languageData = array();

        if (empty($languages)) {
            throw new \Exception('No languages provided', self::STATUS_GENERAL_ERROR);
        }

        foreach ($languages as $language) {
            $helper = $this->getHelper();
            $language_codes = $helper->getLanguageMapping($language->getLanguageCode(), $helper::ERP_TO_MAGENTO);

            foreach ($language_codes as $language_code) {
                if (isset($this->_languageStores[$language_code])) {
                    $language->setLanguageCode($language_code);
                    $this->_languageData[$language_code] = $language;
                }
            }
        }

        if (empty($this->_languageData)) {
            throw new \Exception('Languages for the branding provided do not match any stores in the system', self::STATUS_GENERAL_ERROR);
        }
    }

    /**
     * Updates Meta Information
     * @param \Epicor\Common\Model\Xmlvarien $languageData
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processMetaInformation($languageData, $product)
    {
        if (!$this->isUpdateable('metainformation_update')) {
            return;
        }

        $metaInformation = $languageData->getMetaInformation();
        if ($metaInformation instanceof \Epicor\Common\Model\Xmlvarien) {
            $data = $metaInformation->getData();
            if (isset($data['title'])) {
                $product->setMetaTitle($data['title']);
            }
            if (isset($data['keywords'])) {
                $product->setMetaKeyword($data['keywords']);
            }
            if (isset($data['description'])) {
                $product->setMetaDescription($data['description']);
            }
        }
    }

    function deleteProduct($productId, $storeId)
    {

        $product = $this->catalogProductFactory->create()
            ->setStoreId($storeId)
            ->load($productId);

        $product->setName(false);
        $product->setDescription(false);
        $product->setShortDescription(false);
        $product->setEccRelatedDocuments(false);
        $product->setStoreId($storeId)->save();

        $baseProduct = $this->catalogProductFactory->create()
            ->setStoreId(0)
            ->load($productId);

        $images = $baseProduct->getEccErpImages();

        foreach ($baseProduct->getEccErpImages() as $x => $image) {
            $key = array_search($storeId, $image['stores']);
            if ($key !== false) {
                unset($images[$x]['stores'][$key]);
                unset($images[$x]['store_info'][$storeId]);
            }
        }

        $baseProduct->setEccPreviousErpImages($baseProduct->getEccErpImages());
        $baseProduct->setEccErpImages($images);
        $baseProduct->setStoreId(0)->save();
    }

    private function updateProduct($productId, $languageData, $storeId)
    {
        $product = $this->catalogProductFactory->create()
            ->setStoreId($storeId)
            ->load($productId);
        /* @var $product \Magento\Catalog\Model\Product */
        
        //WSO-4847 :- fix for Overwritten on Update START

//        foreach ($product->getTypeInstance()->getEditableAttributes($product) as $attribute) {
//            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
//            //Unset data if object attribute has no value in current
//            if (!$attribute->isStatic() && !$product->getExistsStoreValueFlag($attribute->getAttributeCode()) && !$attribute->getIsGlobal()) {
//                $product->setData($attribute->getAttributeCode(), false);
//            }
//        }
        
        //WSO-4847 :- fix for Overwritten on Update END
        if ($this->isUpdateable('product_languages_update')) { 
            $this->combineData($languageData, $product);
            if($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                $associatedProducts = array_values($product->getTypeInstance()->getChildrenIds($product->getId()));
                $associatedProducts = $associatedProducts[0];
                if (count($associatedProducts) > 0) {
                    foreach ($associatedProducts as $childProduct) {
                        $child = $this->catalogProductFactory->create()->setStoreId($storeId)->load($childProduct);
                        $childSku = $child->getSku();
                        if (in_array($childSku, $this->mappedProductUoms)) {
                            $this->combineData($languageData, $child);
                            $child->save();
                        }
                    }
                }
            }
        }

        $this->_processMetaInformation($languageData, $product);

        $product->setStoreId($storeId)->save();

        //$this->commProductHelper->create()->reindexProduct($product);
        //  $warnings = $this->processImages($combinedProduct->getId(), $this->getStoreId());
        //  $warning = implode($warnings);
        $this->setStatusDescription('');
    }

    /**
     * Combine data from the upload with product data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    private function combineData($erpData, &$product)
    {
        $this->combineDescriptions($erpData, $product);
        $this->combineImageFiles($erpData, $product);
        $this->combineAdditionalFiles($erpData, $product);
    }

    /**
     * Combine Desceriptions data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    private function combineDescriptions($erpData, &$product)
    {

        // Product Title
        if ($this->isUpdateable('product_title_update')) {
            $title = $this->getVarienData('product_title', $erpData);
            if (!empty($title)) {
                $product->setName($title);
            }
            /*
              elseif ($this->hasVarienData('product_title', $erpData)) {
              throw new Exception(
              'Product Title is Blank', self::STATUS_GENERAL_ERROR
              );
              } else {
              throw new Exception(
              $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'title'), self::STATUS_XML_TAG_MISSING
              );
              }
             */
        }

// Product Short Description 
        if ($this->isUpdateable('product_short_text_update')) {
            $shortText = $this->getVarienData('product_short_text', $erpData);
            if (!empty($shortText)) {
                $product->setShortDescription($shortText);
            }
        }

// Product Description 
        if ($this->isUpdateable('product_ecommerce_description_update')) {
            $eCommerceProductDescription = $this->getVarienData('product_ecommerce_description', $erpData);
            if (!empty($eCommerceProductDescription)) {
                $product->setDescription($eCommerceProductDescription);
            }
        }
    }

    /**
     * Combine Image data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    private function combineImageFiles($erpData, &$product)
    {

        if ($this->isUpdateable('image_filename_update')) {

            $newImages = $this->getVarienDataArray('images', $erpData);
            $this->_loadStores($erpData);

            $erpImages = $product->getEccErpImages();

            if (!is_array($erpImages)) {
                $erpImages = array();
            }

            $imageKeys = array();

            // get exisiting filesnames so we can update if necessary
            if (!empty($erpImages)) {
                foreach ($erpImages as $x => $image) {
                    $imageKeys[$image['filename']] = $x;
                }
            }

            $storeId = $product->getStoreId();
            $storeImages = array();

            // loop through new images finding them in / adding them to the current image array
            foreach ($newImages as $newImage) {
                $filename = $this->getVarienData('image_filename', $newImage);
                $description = $this->getVarienData('image_description', $newImage);
                $position = $this->getVarienData('image_order', $newImage);

                $attachmentData = $newImage->getAttachment() ? $newImage->getAttachment()->getData() : array();
                if (!empty($attachmentData)) {
                    $filename = !empty($attachmentData['filename']) ? $attachmentData['filename'] : $filename;
                    $description = !empty($attachmentData['description']) ? $attachmentData['description'] : $description;
                }
                $storeImages[] = $filename;

                preg_match_all("/[a-zA-Z]/", $this->getVarienData('image_type', $newImage), $types);
                $types = isset($types[0]) ? $types[0] : null;

                if (isset($imageKeys[$filename])) {

                    $existingImage = $erpImages[$imageKeys[$filename]];
                    $existingImage = array_merge($existingImage, $attachmentData);

                    if (isset($existingImage['stores']) && !in_array($storeId, $existingImage['stores'])) {
                        if (!isset($existingImage['stores'])) {
                            $existingImage['stores'] = array();
                        }
                        if (!isset($existingImage['store_info'])) {
                            $existingImage['store_info'] = array();
                        }
                        $existingImage['stores'][] = $storeId;
                        $existingImage['store_info'][$storeId] = array(
                            'description' => $description,
                            'types' => $types,
                            'position' => $position,
                            'STK' => 0,
                            'STT' => 1
                        );
                    } else {
                        $existingImage['store_info'][$storeId]['STT'] = 1;
                        if ($this->isUpdateable('product_languages_language_images_image_description_update')) {
                            $existingImage['store_info'][$storeId]['description'] = $description;
                        }
                        $existingImage['store_info'][$storeId]['types'] = $types;
                        $existingImage['store_info'][$storeId]['position'] = $position;
                    }

                    $erpImages[$imageKeys[$filename]] = $existingImage;
                } else {
                    $erpImages[] = array_merge(array(
                        'description' => $description,
                        'filename' => $filename,
                        'types' => $types,
                        'position' => $position,
                        'media_filename' => null,
                        'stores' => array($storeId),
                        'store_info' => array(
                            $storeId => array(
                                'description' => $description,
                                'types' => $types,
                                'position' => $position,
                                'STK' => 0,
                                'STT' => 1
                            )
                        ),
                        'status' => 0
                        ), $attachmentData);
                }
            }

            // loop through and remove stores from any images not provided in this STK
            // As long as they've not been sent in an STK
            if (!empty($erpImages)) {
                foreach ($erpImages as $x => $image) {
                    if (!in_array($image['filename'], $storeImages)) {
                        $key = array_search($storeId, $image['stores']);
                        if ($key !== false) {
                            if (!$erpImages[$x]['store_info'][$storeId]['STK']) {
                                unset($erpImages[$x]['stores'][$key]);
                                unset($erpImages[$x]['store_info'][$storeId]);
                            } else {
                                $erpImages[$x]['store_info'][$storeId]['STT'] = 0;
                                $erpImages[$x]['store_info'][$storeId]['description'] = isset($erpImages[$x]['description']) ? $erpImages[$x]['description'] : '';
                                $erpImages[$x]['store_info'][$storeId]['types'] = isset($erpImages[$x]['types']) ? $erpImages[$x]['types'] : '';//$erpImages[$x]['types'];
                                $erpImages[$x]['store_info'][$storeId]['position'] = isset($erpImages[$x]['position']) ? $erpImages[$x]['position'] : '';//$erpImages[$x]['position'];
                            }
                        }
                    }
                }
            }

            $product->setEccPreviousErpImages($product->getEccErpImages());
            $product->setEccErpImagesProcessed(0);
            $product->setEccErpImages($erpImages);
            /* To copy Erp images data into associated group products child */
            if($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE){
                $associatedProductIds = array_values($product->getTypeInstance()->getChildrenIds($product->getId()));
                $associatedProductIds = $associatedProductIds[0];
                if(is_array($associatedProductIds) && count($associatedProductIds)>0){
                     foreach($associatedProductIds as $productId){
                            $childproduct = $this->catalogProductFactory->create()->setStoreId(0)->load($productId);
                            $childproduct->setEccErpImagesProcessed(0);
                            $childproduct->setEccPreviousErpImages($product->getEccErpImages());
                            $childproduct->setEccErpImages($erpImages);
                            $childproduct->save();
                    }
                }
            }
        }
    }

    /**
     * Combine related documents
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     * 
     */
    private function combineAdditionalFiles($erpData, &$product)
    {

        if ($this->isUpdateable('related_document_filename_update')) {
            $docSyncRequired = false;
            $documents = $this->_getGroupedData('related_documents', 'related_document', $erpData);

            $product_related_documents = $product->getEccRelatedDocuments();

            if (!is_array($product_related_documents))
                $product_related_documents = array();

            foreach ($product_related_documents as $key => $related_document) {
                if ($related_document['is_erp_document'])
                    unset($product_related_documents[$key]);
            }

            foreach ($documents as $document) {
                if ($document) {
                    $description = $document->getDescription();
                    $url = $attachmentNumber = $erpFileId = $webFileId = $attachmentStatus = '';
                    $filename = $document->getFilename();
                    $syncRequired = 'N';
                    if ($document->getAttachment()) {
                        $description = $document->getAttachment()->getDescription();
                        $url = $document->getAttachment()->getUrl();
                        $filename = $document->getAttachment()->getFilename();
                        $attachmentNumber = $document->getAttachment()->getAttachmentNumber();
                        $erpFileId = $document->getAttachment()->getErpFileId();
                        $webFileId = $document->getAttachment()->getWebFileId();
                        $attachmentStatus = $document->getAttachment()->getAttachmentStatus();
                    }

                    if ($url) {
                        $filename = $url;
                    }
                    
                    if ($erpFileId || $attachmentStatus == "R") {
                        $docSyncRequired = true;
                        $syncRequired = 'Y';
                    }

                    $product_related_documents[] = array(
                        'description' => $description,
                        'filename' => $filename,
                        'url' => $url,
                        'is_erp_document' => '1',
                        'attachment_number' => $attachmentNumber,
                        'erp_file_id' => $erpFileId,
                        'web_file_id' => $webFileId,
                        'attachment_status' => $attachmentStatus,
                        'sync_required' => $syncRequired 
                    );
                }
            }
            if ($docSyncRequired) {
                $product->setData('ecc_related_documents_synced', 0);
            }
            sort($product_related_documents);

            $product->setEccRelatedDocuments($product_related_documents);
        }
    }

}
