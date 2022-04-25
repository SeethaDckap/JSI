<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;

use Magento\Framework\App\ObjectManager;
/**
 * Response ALT - Update alternatives record
 * 
 * Update the alternatives information for the specified productCode
 * 
 * XML Data Support
 * productCode          - supported
 * delete               - supported
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Alt extends \Epicor\Comm\Model\Message\Upload
{
    private $_relatedUpdates = array(
        'added' => array(),
        'deleted' => array()
    );
    private $_crossUpdates = array(
        'added' => array(),
        'deleted' => array()
    );
    private $_upsellUpdates = array(
        'added' => array(),
        'deleted' => array()
    );
    private $_substituteUpdates = array(
        'added' => array(),
        'deleted' => array()
    );

    protected $productLinkFactory;
    protected $linkTypeProvider;
    protected $linkResource;
    private $_productLinks;
    private $_relatedProducts;
    private $_crossProducts;
    private $_upsellProducts;
    private $_substituteProducts;
    private $_allowedTypes;
    private $_currentRelatedData;
    private $_currentCrossSellData;
    private $_currentUpSellData;
    private $_currentSubstituteData;
    private $_related_product_missing;
    protected $_maxDeadlockRetriesDefault = 5;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Model\Substitute
     */
    protected $substituteLinkType;

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;

   public function __construct(
       \Epicor\Comm\Model\Context $context,
       \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
       \Magento\Catalog\Model\ResourceModel\Product\Link $linkResource,
       \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
       \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
       \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
       \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
       \Epicor\Comm\Model\Substitute $substituteLinkType = null,
       array $data = [])
   {
       parent::__construct($context, $resource, $resourceCollection, $data);
       $this->commProductHelper = $context->getCommProductHelper();
       $this->catalogProductFactory = $context->getCatalogProductFactory();
       $this->commEntityregHelper = $commEntityregHelper;
       $this->setConfigBase('epicor_comm_field_mapping/alt_mapping/');
       $this->setMessageType('ALT');
       $this->setLicenseType(array('Consumer', 'Customer'));
       $this->setStatusCode(self::STATUS_SUCCESS);
       $this->setMessageCategory(self::MESSAGE_CATEGORY_PRODUCT);
       $this->setStatusDescription('');
       $this->linkResource = $linkResource;
       $this->productLinkFactory = $productLinkFactory;
       $this->linkTypeProvider = $linkTypeProvider;
       $this->substituteLinkType = $substituteLinkType
           ?: ObjectManager::getInstance()->get(\Epicor\Comm\Model\Substitute::class);
       $this->_allowedTypes = array(
           $this->getConfig('alt_related') => 'related',
           $this->getConfig('alt_cross') => 'cross_sell',
           $this->getConfig('alt_up') => 'up_sell',
           $this->getConfig('alt_sub') => 'substitute'
       );

   }
    public function resetProcessFlags()
    {
        parent::resetProcessFlags();

        $this->_relatedUpdates = array(
            'added' => array(),
            'deleted' => array()
        );

        $this->_crossUpdates = array(
            'added' => array(),
            'deleted' => array()
        );

        $this->_upsellUpdates = array(
            'added' => array(),
            'deleted' => array()
        );
        $this->_substituteUpdates = array(
            'added' => array(),
            'deleted' => array()
        );
        $this->_productLinks = null;
        $this->_relatedProducts = null;
        $this->_crossProducts = null;
        $this->_upsellProducts = null;
        $this->_substituteProducts = null;
        $this->_currentRelatedData = null;
        $this->_currentCrossSellData = null;
        $this->_currentUpSellData = null;
        $this->_currentSubstituteData = null;
        $this->_related_product_missing = null;
    }

    /**
     * Processes the upload request
     * 
     * @throws \Exception
     */
    public function processAction()
    {
        // Set ERP product code as magentoId (SKU)
        $this->erpData = $this->getRequest();
        $productCode = $this->getVarienData('product_code');
        $this->setMessageSubject($productCode);

        $product = $this->getProduct('product_code', $this->erpData);
        /** @var $product \Epicor\Comm\Model\Product */

        if (!$product->isObjectNew()) {
            $this->initCrossSellIds($product);
            $this->initRelatedIds($product);
            $this->initUpSellIds($product);
            $this->initSubstituteIds($product);

            $deleteFlag = $this->getVarienDataFlag('alternative_delete');
            $altProducts = $this->getVarienDataArray('products_products');

            if ($deleteFlag) {
                foreach ($altProducts as $altProduct) {
                    $this->unAssignProductByType($altProduct,$product);
                }
            } else {
                $this->backupCurrentRelatedData($product);
                $this->_productLinks = $product->getProductLinks();
                foreach ($altProducts as $altProduct) {
                    $this->assignProductByType($altProduct,$product);
                }
            }
           // $product->setRelatedLinkData($this->_relatedProducts);
           // $product->setUpSellLinkData($this->_upsellProducts);
           // $product->setCrossSellLinkData($this->_crossProducts);
           // $product->setSubstituteLinkData($this->_crossProducts);

            $product->setProductLinks($this->_productLinks);
            
            $product->setStoreId(0)->save();
            $this->updateEntityRegistrations($product->getId());
           // $productHelper = $this->commProductHelper->create();
           // $productHelper->reindexProduct($product);
            if ($this->_related_product_missing) {
                $invalid_codes = implode(', ', $this->_related_product_missing);
                $this->setStatusDescription($this->getWarningDescription(self::STATUS_RELATED_PRODUCT_NOT_ON_FILE) . ": " . $invalid_codes);
                $this->setStatusCode(self::STATUS_RELATED_PRODUCT_NOT_ON_FILE);
            }
        } else {
            if ($this->hasVarienData('product_code')) {
                throw new \Exception(
                $this->getErrorDescription(self::STATUS_PRODUCT_NOT_ON_FILE, $productCode), self::STATUS_PRODUCT_NOT_ON_FILE
                );
            } else {
                throw new \Exception(
                $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'productCode'), self::STATUS_XML_TAG_MISSING
                );
            }
        }
    }

    /**
     * Inits the cross sell product id's for the provided product
     * 
     * @param \Magento\Catalog\Model\Product $product
     */
    private function initCrossSellIds($product)
    {
        $data = array();
        foreach ($product->getCrossSellLinkCollection() as $link) {
            $data[$link->getLinkedProductId()]['position'] = $link->getPosition();
        }
        $this->_crossProducts = $data;
    }

    /**
     * Inits the Up sell product id's for the provided product
     * 
     * @param \Magento\Catalog\Model\Product $product
     */
    private function initUpSellIds($product)
    {
        $data = array();
        foreach ($product->getUpSellLinkCollection() as $link) {
            $data[$link->getLinkedProductId()]['position'] = $link->getPosition();
        }
        $this->_upsellProducts = $data;
    }

    /**
     * Inits the Substitute product id's for the provided product
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    private function initSubstituteIds($product)
    {
        $data = array();
        foreach ($this->substituteLinkType->getSubstituteLinkCollection($product) as $link) {
            $data[$link->getLinkedProductId()]['position'] = $link->getPosition();
        }
        $this->_substituteProducts = $data;
    }

    /**
     * Inits the related product id's for the provided product
     * 
     * @param \Magento\Catalog\Model\Product $product
     */
    private function initRelatedIds($product)
    {
        $data = array();
        foreach ($product->getRelatedLinkCollection() as $link) {
            $data[$link->getLinkedProductId()]['position'] = $link->getPosition();
        }
        $this->_relatedProducts = $data;
    }

    /**
     * Loads a product
     * 
     * @param string $config
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @return \Magento\Catalog\Model\Product
     * 
     * @throws \Exception
     */
    private function getProduct($config, $erpData)
    {
        $productCode = $this->getVarienData($config, $erpData);
        $product = $this->catalogProductFactory->create();
        $productId = $this->catalogProductFactory->create()->getIdBySku($productCode);
        if (!$productId) {
            $this->_related_product_missing[] = $productCode;
        } else {
            $product = $product->load($productId);
        }
        return $product;
    }

 
    /**
     * Assigns a related / cross sell / up sell product to the target product
     * 
     * @param \Epicor\Common\Model\Xmlvarien $altProduct
     */
    private function assignProductByType($altProduct, $orgProduct)
    {
        $product = $this->getProduct('products_product_code', $altProduct);

        // If alt item was found
        if ($product->getId()) {
            //only populate if weighting update is allowed in config
            $altWeight = $this->getVarienData('products_product_weighting', $altProduct);
            $id = $product->getId();
            $linkType = $this->getMagentoType($altProduct);

            $entry = array(
                'position' => $altWeight
            );
            
            //$link = $this->productLinkFactory;
            switch ($linkType) {
                case 'related':
                    if ($this->isUpdateable('product_weighting_update', (isset($this->_currentRelatedData[$product->getId()])))) {
                        $this->_relatedUpdates['added'][] = $id;
                        $this->_relatedProducts[$id] = $entry;
                        $link = $this->productLinkFactory->create();
                        $link->setSku($orgProduct->getSku())
                            ->setLinkedProductSku($product->getSku())
                            ->setLinkType("related")
                            ->setPosition($altWeight ? (int)$altWeight : 0);
                        $this->_productLinks[] = $link;
                    }
                    break;
                case 'cross_sell':
                    if ($this->isUpdateable('product_weighting_update', (isset($this->_currentCrossSellData[$product->getId()])))) {
                        $this->_crossUpdates['added'][] = $id;
                        $this->_crossProducts[$id] = $entry;
                         $link = $this->productLinkFactory->create();
                        $link->setSku($orgProduct->getSku())
                            ->setLinkedProductSku($product->getSku())
                            ->setLinkType("crosssell")
                            ->setPosition($altWeight ? (int)$altWeight : 0);
                        $this->_productLinks[] = $link;
                        break;
                    }
                case 'up_sell':
                    if ($this->isUpdateable('product_weighting_update', (isset($this->_currentUpSellData[$product->getId()])))) {
                        $this->_upsellUpdates['added'][] = $id;
                        $this->_upsellProducts[$id] = $entry;

                         $link = $this->productLinkFactory->create();
                        $link->setSku($orgProduct->getSku())
                            ->setLinkedProductSku($product->getSku())
                            ->setLinkType("upsell")
                            ->setPosition($altWeight ? (int)$altWeight : 0);
                        $this->_productLinks[] = $link;
                    }
                    break;
                case 'substitute':
                    if ($this->isUpdateable('product_weighting_update', (isset($this->_currentSubstituteData[$product->getId()])))) {
                        $this->_substituteUpdates['added'][] = $id;
                        $this->_substituteProducts[$id] = $entry;

                        $link = $this->productLinkFactory->create();
                        $link->setSku($orgProduct->getSku())
                            ->setLinkedProductSku($product->getSku())
                            ->setLinkType("substitute")
                            ->setPosition($altWeight ? (int)$altWeight : 0);
                        $this->_productLinks[] = $link;
                    }
                    break;
            }
        }
    }

    
    /**
     * unassigns a related / cross sell / up sell product from the target product
     * 
     * @param \Epicor\Common\Model\Xmlvarien $altProduct
     */
    private function unAssignProductByType($altProduct, $orgProduct)
    {
        $product = $this->getProduct('products_product_code', $altProduct);
        $id = $product->getId();
        $linkType = $this->getMagentoType($altProduct);
        
        $linkTypesToId = $this->linkTypeProvider->getLinkTypes();
        
        switch ($linkType) {
            case 'related':
                $this->_relatedUpdates['deleted'][] = $id;
                unset($this->_relatedProducts[$id]);
                
                $linkId = $this->linkResource->getProductLinkId(
                    $orgProduct->getId(),
                    $product->getId(),
                    $linkTypesToId["related"]
                );
                if($linkId){
                    $this->linkResource->deleteProductLink($linkId);
                }
                break;
            case 'cross_sell':
                $this->_crossUpdates['deleted'][] = $id;
                unset($this->_crossProducts[$id]);
                
                $linkId = $this->linkResource->getProductLinkId(
                    $orgProduct->getId(),
                    $product->getId(),
                    $linkTypesToId["crosssell"]
                );
                
                if($linkId){
                    $this->linkResource->deleteProductLink($linkId);
                }
                break;
            case 'up_sell':
                $this->_upsellUpdates['deleted'][] = $id;
                unset($this->_upsellProducts[$id]);
                
                $linkId = $this->linkResource->getProductLinkId(
                    $orgProduct->getId(),
                    $product->getId(),
                    $linkTypesToId["upsell"]
                );
                
                if($linkId){
                    $this->linkResource->deleteProductLink($linkId);
                }
                break;
            case 'substitute':
                $this->_substituteUpdates['deleted'][] = $id;
                unset($this->_substituteProducts[$id]);

                $linkId = $this->linkResource->getProductLinkId(
                    $orgProduct->getId(),
                    $product->getId(),
                    $linkTypesToId["substitute"]
                );

                if($linkId){
                    $this->linkResource->deleteProductLink($linkId);
                }
                break;
        }
    }

    /**
     * gets the product relation type from the alt > magento mapping
     * 
     * @param \Epicor\Common\Model\Xmlvarien $altProduct
     * 
     * @return string
     * 
     * @throws \Exception
     */
    private function getMagentoType($altProduct)
    {
        $altType = $this->getVarienData('products_product_type', $altProduct);
        $cleanedAltType = $altType ?: 'A';
        $mageType = $this->_allowedTypes[$cleanedAltType];
        if (empty($mageType)) {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_TYPE, 'Alternative', $altType), self::STATUS_INVALID_TYPE);
        }
        return $mageType;
    }

    private function updateEntityRegistrations($productId)
    {
        $this->updateEntityReg($productId, $this->_relatedUpdates['added'], 'Related');
        $this->updateEntityReg($productId, $this->_crossUpdates['added'], 'CrossSell');
        $this->updateEntityReg($productId, $this->_upsellUpdates['added'], 'UpSell');
        $this->updateEntityReg($productId, $this->_substituteUpdates['added'], 'substitute');

        $this->removeEntityReg($productId, $this->_relatedUpdates['deleted'], 'Related');
        $this->removeEntityReg($productId, $this->_crossUpdates['deleted'], 'CrossSell');
        $this->removeEntityReg($productId, $this->_upsellUpdates['deleted'], 'UpSell');
        $this->removeEntityReg($productId, $this->_substituteUpdates['deleted'], 'substitute');
    }

    private function updateEntityReg($productId, $childIds, $type)
    {
        if (!empty($childIds)) {
            $helper = $this->commEntityregHelper;
            /* @var $helper Epicor_Comm_Helper_Entityreg */

            foreach ($childIds as $childId) {
                $helper->updateEntityRegistration($productId, $type, $childId);
            }
        }
    }

    private function removeEntityReg($productId, $childIds, $type)
    {
        if (!empty($childIds)) {
            $helper = $this->commEntityregHelper;
            /* @var $helper Epicor_Comm_Helper_Entityreg */

            foreach ($childIds as $childId) {
                $helper->removeEntityRegistration($productId, $type, $childId);
            }
        }
    }

    private function backupCurrentRelatedData($parentProduct)
    {
        $parentProduct = $this->catalogProductFactory->create()->load($parentProduct->getId());
        $relatedProducts = $parentProduct->getRelatedProducts();
        foreach ($relatedProducts as $relatedProduct) {
            $this->_currentRelatedData[$relatedProduct->getId()]['position'] = $relatedProduct->getPosition();
        }
        $crossSellProducts = $parentProduct->getCrossSellProducts();
        foreach ($crossSellProducts as $crossSellProduct) {
            $this->_currentCrossSellData[$crossSellProduct->getId()]['position'] = $crossSellProduct->getPosition();
        }
        $upSellProducts = $parentProduct->getUpSellProducts();
        foreach ($upSellProducts as $upSellProduct) {
            $this->_currentUpSellData[$upSellProduct->getId()]['position'] = $upSellProduct->getPosition();
        }
        $substituteProducts = $this->substituteLinkType->getSubstituteProducts($parentProduct);
        foreach ($substituteProducts as $substituteProduct) {
            $this->_currentSubstituteData[$substituteProduct->getId()]['position'] = $substituteProduct->getPosition();
        }
    }

}