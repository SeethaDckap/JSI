<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Message\Upload;


/**
 * List Upload Abstract Class
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
abstract class AbstractModel extends \Epicor\Comm\Model\Message\Upload
{

    protected $_brands;
    protected $_listModel;
    protected $_erpOverride;
    protected $_exists;
    protected $_listType = 'list';

    /**
     * @var \Epicor\Lists\Helper\Messaging\Customer
     */
    protected $listsMessagingCustomerHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;
    
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        array $data = [])
    {
        $this->listsMessagingCustomerHelper = $listsMessagingCustomerHelper;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->listsListModelFactory = $listsListModelFactory;
        $this->catalogResourceModelProductFactory = $context;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setLicenseType('Customer');
        $this->setMessageCategory(self::MESSAGE_CATEGORY_LIST);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->setDelimiter($this->listsMessagingCustomerHelper->getUOMSeparator());
    }


    /**
     * Process a request
     *
     * @param array $requestData
     * @return $this
     */
    public function processAction()
    {
        $this->setMessageSubject($this->getListCode());
        $erpData = $this->getErpData();

        $delete = $erpData->getData('_attributes')->getDelete();
        if ($delete == 'Y') {
            $this->getList();
            $this->deleteList();
        } else {
            $this->processList();
            if ($this->_abandonUpload) {
                throw new \Exception(implode(' | ', $this->_returnMessages), \Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR);
            } else {
                $this->getList()->save();
                if ($this->_returnMessages) {
                    $this->setStatusDescription(implode(' | ', $this->_returnMessages));
                }
            }
        }

        return $this;
    }

    /**
     * Processes List
     *
     * @return $this
     */
    protected function processList()
    {

        if (!$this->_abandonUpload) {
            $this->processListDetails();
            $this->processErpAccounts();
            if (!$this->_abandonUpload) {
                $this->processBrands();
                $this->processStores();
                $this->processProducts();
            }
        }


        return $this;
    }

    protected function getAccountAttributes()
    {
        $erpData = $this->getErpData();
        if ($erpData && $erpData->getAccounts()) {
            return $erpData->getAccounts()->getData('_attributes');
        }
    }

    private function noErpAccountsSet($accountAttributes, $erpAccountCount): bool
    {
        return !$accountAttributes && $erpAccountCount === 0;
    }

    private function processCupgErpAccounts($list, $erpAccountCount)
    {
        $accountAttributes = $this->getAccountAttributes();
        if ($this->noErpAccountsSet($accountAttributes, $erpAccountCount)) {
            $list->setErpAccountLinkType('N');
            return true;
        }
        $accountExclude = $accountAttributes ? $accountAttributes->getExclude() : 'N';
        if ($accountExclude == 'N' && $erpAccountCount == 0) {
            /* exclude = N and 0 erp accounts: Not valid */
            $this->_returnMessages[] = "No ERP accounts specified, 1 or more is required.";
            $this->_abandonUpload = true;
            return true;
        }

        return false;
    }

    /**
     * Processes List ERP Accounts
     *
     * @return $this
     */
    private function processErpAccounts()
    {
        if (!$this->isUpdateable('erp_accounts_update', $this->listExists(), 'erpaccounts')) {
            return $this;
        }

        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */
        $erpAccounts = $this->getErpAccounts();
        $erpAccountCount = count(array_filter($erpAccounts, 'strlen'));
        /* check if message type is CUPG and checks for attribute exclude */
        if ($this->getMessageType() === 'CUPG' && $this->processCupgErpAccounts($list, $erpAccountCount)) {
            return $this;
        }

        $addAccounts = array();
        if ($erpAccountCount > 0) {
            foreach ($this->getCompanies() as $company) {
                foreach ($erpAccounts as $erpAccount) {
                    
                    if(!empty($company)){
                        $account = $this->commResourceCustomerErpaccountCollectionFactory->create()
                                ->addFieldToSelect('entity_id')
                                ->addFieldToFilter('company', array('eq' => $company))
                                ->addFieldToFilter( 
                                   // this is an OR condition. if the value is in account_number OR short_code, it will be returned
                                array('account_number', 'short_code')
                                , array(
                                array('eq' => $erpAccount),
                                array('eq' => $erpAccount)
                                )
                            )->getFirstItem();
                    }else{
                        $account = $this->commResourceCustomerErpaccountCollectionFactory->create()
                                ->addFieldToSelect('entity_id')
                                ->addFieldToFilter( 
                                array('account_number', 'short_code')
                                , array(
                                array('eq' => $erpAccount),
                                array('eq' => $erpAccount)
                                )
                            )->getFirstItem();
                    }
                  
                    if ($account->getEntityId()) {
                        $addAccounts[] = $account->getEntityId();
                    } else {
                        $this->_returnMessages[] = "ERP account: {$erpAccount} is not valid on this system - Cannot process message  ";
                        $this->_abandonUpload = true;
                        return;
                    }
                }
            }
        }
        $list->removeErpAccounts($list->getErpAccounts());
        $list->addErpAccounts($addAccounts);

        return $this;
    }

    /**
     * Processes List Products
     *
     * @return $this
     */
    protected function processProducts()
    {
        if (!$this->isUpdateable('products_update', $this->listExists(), 'products')) {
            return $this;
        }

        $erpProducts = $this->getProducts();

        foreach ($erpProducts as $erpProduct) {
            if ($this->checkProductExists($erpProduct->getProductCode()) == false) {
                continue;
            }

            $this->processUoms($erpProduct);
        }
    }

    /**
     * Process UOM info
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     *
     * @return void
     */
    protected function processUoms($erpProduct)
    {
        $uoms = $this->_getGroupedData('unit_of_measures', 'unit_of_measure', $erpProduct);
        foreach ($uoms as $uom) {
            $uomCode = $this->checkUomExists($uom, $erpProduct->getProductCode());
            if ($uomCode === false) {
                continue;
            }

            $this->processProduct($uomCode, $erpProduct, $uom);
        }
    }

    /**
     * Processes a Product
     *
     * @param string $productCode
     * @param \Magento\Framework\DataObject $erpProduct
     * @return $this
     */
    protected function processProduct($productCode, $erpProduct, $uomData)
    {
        $productId = $this->catalogProductFactory->create()->getIdBySku($productCode);
        if (!$productId) {
            return $this;
        }

        $list = $this->getList();
        /* @var $list \Epicor\Lists\Model\ListModel */

        if ($this->isDeleteAttributeSetYes($erpProduct)) {
            $list->removeProducts($productCode);
        } else {
            $list->addProducts($productCode);
        }

        return $this;
    }

    private function isDeleteAttributeSetYes($erpProduct): bool
    {
        $attributes = $erpProduct->getData('_attributes');

        return $attributes && $attributes->getDelete() === 'Y';
    }

    /**
     * Deletes a List
     *
     * @return $this
     */
    protected function deleteList()
    {
        if (!$this->_listModel->isObjectNew()) {
            $this->_listModel->delete();
        }

        return $this;
    }

    /**
     * Returns the Request Erp Data
     *
     * @return \Magento\Framework\DataObject $erpData
     */
    protected function getErpData()
    {
        if (!$this->_erpData) {
            $this->_erpData = $this->getRequest()->getList();
        }

        return $this->_erpData;
    }

    /**
     * Returns the List Code
     *
     * @return string $listCode
     */
    protected function getListCode()
    {
        return $this->getErpData()->getListCode();
    }

    /**
     * Returns the list being processed
     *
     * @return \Epicor\Lists\Model\ListModel $list
     */
    protected function getList()
    {
        if (!$this->_listModel) {
            $this->_listModel = $this->listsListModelFactory->create()->load($this->getListCode(), 'erp_code');
        }

        return $this->_listModel;
    }

    /**
     * Returns true if Processed List Exists
     *
     * @return bool
     */
    protected function listExists()
    {
        if (!$this->_exists) {
            $this->_exists = !$this->getList()->isObjectNew();
        }

        return $this->_exists;
    }

    /**
     * Returns Message Brands
     *
     * @return \Magento\Framework\DataObject $brands
     */
    protected function getBrands()
    {
        if (!$this->_brands) {
            $this->_brands = $this->_getGroupedData('brands', 'brand', $this->getErpData());
        }

        return $this->_brands;
    }

    /**
     * Returns Message Products
     *
     * @return \Magento\Framework\DataObject $products
     */
    protected function getProducts()
    {
        if (!$this->_products) {
            $this->_products = $this->_getGroupedData('products', 'product', $this->getErpData());
        }

        return $this->_products;
    }

    /**
     * Returns Erp Override values
     *
     * @return array $erpOverride
     */
    protected function getErpOverride()
    {
        if (!$this->_erpOverride) {
            $this->_erpOverride = unserialize($this->getList()->getData('erp_override'));
        }

        return $this->_erpOverride;
    }

    /**
     * Returns Erp Data Accounts
     *
     * @return \Magento\Framework\DataObject $erpAccounts
     */
    protected function getErpAccounts()
    {
        return $this->_getGroupedData('accounts', 'account_number', $this->getErpData());
    }

    /**
     * Process Brands & saves them against the list
     *
     * @return $thhis
     */
    protected function processBrands()
    {
        $list = $this->getList();
        $list->removeBrands($list->getBrands());
        $list->addBrands($this->getBrands());

        return $this;
    }

    /**
     * Process Stores & saves them against the list
     *
     * @return $this
     */
    private function processStores()
    {
        if (!$this->isUpdateable('stores_update', $this->listExists(), 'stores')) {
            return $this;
        }

        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */

        $websites = $this->storeManager->getWebsites();
        $addWebsites = array();
        $addStores = array();
        foreach ($this->getBrands() as $brand) {
            foreach ($websites as $website) {
                if ($this->matchBranding($brand, $website)) {
                    $addWebsites[] = $website->getId();
                }

                foreach ($website->getGroups() as $store) {
                    if ($this->matchBranding($brand, $website, $store)) {
                        $addStores[] = $store->getId();
                    }
                }
            }
        }

        $list->removeWebsites($list->getWebsites());
        $list->removeStoreGroups($list->getStoreGroups());

        $list->addWebsites($addWebsites);
        $list->addStoreGroups($addStores);

        return $this;
    }

    /**
     * Returns true if branding and website (or store) matches
     *
     * @return bool
     */
    protected function matchBranding($brand, $website, $store = false)
    {
        $company = $website->getCompany() ?: ($store ? $store->getCompany() : null);
        $site = $website->getSite() ?: ($store ? $store->getSite() : null);
        $group = $website->getGroup() ?: ($store ? $store->getGroup() : null);
        $warehouse = $website->getWarehouse() ?: ($store ? $store->getWarehouse() : null);

        if (
            (!$company || !$brand->getCompany() || $company == $brand->getCompany()) &&
            (!$site || !$brand->getSite() || $site == $brand->getSite()) &&
            (!$warehouse || !$brand->getWarehouse() || $warehouse == $brand->getWarehouse()) &&
            (!$group || !$brand->getGroup() || $group == $brand->getGroup())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns branding Companies
     *
     * @return array $companies
     */
    protected function getCompanies()
    {
        $companies = array();
        foreach ($this->getBrands() as $brand) {
            if (isset($brand['company'])) {
                $companies[$brand['company']] = $brand['company'];
            }
        }
        if (!$companies) {
            $defaultCompany = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId())->getCompany();
            $companies[$defaultCompany] = $defaultCompany;
        }

        return $companies;
    }

    /**
     * Returns true if data is meant to be updateable
     *
     * @param string $config
     * @param bool $exists
     * @param string $override
     * @return bool
     */
    public function isUpdateable($config, $exists = true, $override = false)
    {
        if ($exists && $override) {
            $erpOverride = $this->getErpOverride();
            if (isset($erpOverride[$override]) && !is_null($erpOverride[$override]) && $erpOverride[$override] != '') {
                return $erpOverride[$override] == '1' ? true : false;
            }
        }

        return parent::isUpdateable($config, $exists);
    }

    /**
     * Checks whether an sku exists
     *
     * @param string $product
     *
     * @return boolean
     */
    public function checkProductExists($product)
    {
        //check product exists
        $productId = $this->catalogProductFactory->create()->getIdBySku($product);
        if (!$productId) {
            $this->_returnMessages[] = "Product: {$product} is not valid on this system - Product ignored.  ";
            return false;
        }
        return true;
    }

    /**
     * Checks whether a code & uom combo exist
     *
     * @param \Epicor\Common\Model\Xmlvarien $uomData
     * @param string $mainProductCode
     *
     * @return boolean|string
     */
    public function checkUomExists($uomData, $mainProductCode)
    {
        $mainProductId = $this->catalogProductFactory->create()->getIdBySku($mainProductCode);
       // $productResource = $this->catalogResourceModelProductFactory->create();
        /* @var $productResource Mage_Catalog_Model_Resource_Product */
        $productObj = $this->catalogResourceModelProductCollectionFactory->create()
                ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('eq' => $mainProductId))->getFirstItem();

        $typeId = $productObj->getTypeId();

        if ($typeId == 'grouped') {
            $uomProductCode = $mainProductCode . $this->getDelimiter() . $uomData->getUnitOfMeasureCode();
            $uomExists = $this->catalogProductFactory->create()->getIdBySku($uomProductCode);
            if ($uomExists) {
                return $uomProductCode;
            } else {
                $this->_returnMessages[] = "Product: {$mainProductCode}, UOM {$uomData->getUnitOfMeasureCode()} does not exist and has not been created for this {$this->_listType}.  ";
                return false;
            }
        } else {
            $uom = $productObj->getEccUom();
            if ($uomData->getUnitOfMeasureCode() != $uom) {
                $this->_returnMessages[] = "Product: {$mainProductCode}, UOM : {$uomData->getUnitOfMeasureCode()} does not exist and has not been created for {$this->_listType}.  ";
                return false;
            }
        }

        return $mainProductCode;
    }

}
