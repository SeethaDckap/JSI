<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Message\Upload;


/**
 * Response CUPG - Upload Product Group
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Cccn extends \Epicor\Lists\Model\Message\Upload\AbstractModel
{

    protected $_listType = 'Contract';


    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $catalogResourceModelProductLink;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    protected $_products;
    
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Link $catalogResourceModelProductLink,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->catalogResourceModelProductLink = $catalogResourceModelProductLink;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;

        parent::__construct($context, $listsMessagingCustomerHelper, $commResourceCustomerErpaccountCollectionFactory, $listsListModelFactory, $resource, $resourceCollection, $catalogResourceModelProductCollectionFactory, $data);

        $this->setConfigBase('epicor_comm_field_mapping/cccn_mapping/');
        $this->setMessageType('CCCN');
    }


    public function processList()
    {
        parent::processList();
        if (!$this->_abandonUpload) {
            $this->processContract();
            $this->processAddresses();
        }

        return $this;
    }

    /**
     * Process List Info & saves them against the list
     *
     * @return void
     */
    protected function processListDetails()
    {
        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */
        $erpData = $this->getErpData();

        $exists = $this->listExists();

        if (!$exists) {
            $list->setErpCode($this->getListCode());
            $list->setType('Co');
            $list->setErpAccountLinkType('E');
            $list->setSource('erp');
            $list->setLabel($erpData->getContractTitle());
        }

        if ($this->isUpdateable('title_update', $exists, 'title')) {
            $list->setTitle($erpData->getContractTitle());
        }

        if ($this->isUpdateable('default_currency_update', $exists, 'default_currency')) {
            $currencyCode = $this->commMessagingHelper->create()->getCurrencyMapping($erpData->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        }

        if ($this->isUpdateable('start_date_update', $exists, 'start_date')) {
            $list->setStartDate($erpData->getStartDate());
        }

        if ($this->isUpdateable('end_date_update', $exists, 'end_date')) {
            $list->setEndDate($erpData->getEndDate());
        }

        if ($erpData->getContractStatus() == 'I') {
            $list->setActive(false);
        } else if ($this->isUpdateable('contract_status_update', $exists, 'contract_status')) {
            $list->setActive($erpData->getContractStatus() == 'A');
        }


        if ($this->isUpdateable('contract_description_update', $exists, 'description')) {
            $list->setDescription($erpData->getContractDescription());
        }

        $list->setUpdateDate($erpData->getLastModifiedDate());
    }

    /**
     * Process Contract Info & saves them against the list
     *
     * @return void
     */
    protected function processContract()
    {
        $contract = $this->getList()->getContract();
        /* @var $contract Epicor_Lists_Model_Contract */
        $erpData = $this->getErpData();

        $contractCode = $erpData->getContractCode();
        $accountNumber = $erpData->getAccountNumber();
        $this->setMessageSubject($accountNumber . '-' . $contractCode);

        $exists = !$contract->isObjectNew();

        if ($this->isUpdateable('sales_rep_update', $exists, 'sales_rep')) {
            $contract->setSalesRep($erpData->getSalesRep());
        }

        if ($this->isUpdateable('contact_update', $exists, 'contact')) {
            $contract->setContactName($erpData->getContactName());
        }

        if ($this->isUpdateable('purchase_order_number_update', $exists, 'purchase_order_number')) {
            $contract->setPurchaseOrderNumber($erpData->getPurchaseOrderNumber());
        }

        if ($this->isUpdateable('last_modified_date_update', $exists, 'last_modified_date')) {
            $contract->setLastModifiedDate($erpData->getLastModifiedDate());
        }

        $contract->setContractStatus($erpData->getContractStatus());

        return $this;
    }

    /**
     * Process Addresses & saves them against the list
     *
     * @return void
     */
    protected function processAddresses()
    {
        if (!$this->isUpdateable('addresses_update', $this->listExists(), 'addresses')) {
            return $this;
        }


        $erpAddresses = $this->_getGroupedData('delivery_addresses', 'delivery_address', $this->getErpData());

        $addressCodes = array();
        foreach ($erpAddresses as $address) {
            if (in_array($address->getAddressCode(), $addressCodes)) {
                $this->_returnMessages[] = " Repeated Address Code: {$address->getAddressCode()} in CCCN - Unable to continue processing. ";
                $this->_abandonUpload = true;
            }
            $addressCodes[$address->getAddressCode()] = $address->getAddressCode();
        }
        if (!$this->_abandonUpload) {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */
            $list->removeAddresses($list->getAddresses());
            $list->addAddresses($erpAddresses);
        }

        return $this;
    }

    /**
     * Processes a Product
     *
     * @param string $productCode
     * @param \Magento\Framework\DataObject $erpProduct
     * @param \Magento\Framework\DataObject $uomData
     * @return $this
     */
    protected function processProduct($productCode, $erpProduct, $uomData)
    {
        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */
        if ($erpProduct->getData('_attributes')->getDelete() == 'Y') {
            $list->removeProducts($productCode);
            return $this;
        }

        $listProduct = $this->dataObjectFactory->create();
        $listProduct->setId($productCode);
        $listProduct->setSku($productCode);

        if ($uomData) {
            $listProduct->setQty($uomData->getContractQty());

            $erpCurrencies = $this->_getGroupedData('currencies', 'currency', $uomData);
            $currencies = array();
            foreach ($erpCurrencies as $erpCurrency) {
                $currencies[] = $this->processProductCurrency($productCode, $erpCurrency);
            }
            $list->setProductPrices($productCode, $currencies);
        }

        $list->addProducts($listProduct);

        $this->processProductContract($productCode, $erpProduct, $uomData);
        $this->checkProductGroupedParents($productCode, $erpProduct, $uomData);

        return $this;
    }

    /**
     * Checks to see if we need to link contract data to a parent item
     *
     * @param string $productCode
     *
     * @return null
     */
    protected function checkProductGroupedParents($productCode, $erpProduct, $uomData)
    {
        $productId = $this->catalogProductFactory->create()->getIdBySku($productCode);

        if (empty($productId)) {
            return;
        }

        $parentIds = $this->catalogResourceModelProductLink->getParentIdsByChild($productId, \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);

        if (empty($parentIds)) {
            return;
        }

        $productsCollection = $this->catalogResourceModelProductCollectionFactory->create()
            ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
            ->addAttributeToFilter('entity_id', array('in' => $parentIds))
            ->setFlag('no_product_filtering', true);
        $skus = $productsCollection->getColumnValues('sku');
        if (empty($skus) == false) {
            foreach ($skus as $sku) {
                $parentData = $this->dataObjectFactory->create($erpProduct->getData());
                $parentData->setLineStatus('A');
                $parentData->setEffectiveStartDate(false);
                $parentData->setEffectiveEndDate(false);
                $this->processProductContract($sku, $parentData, $uomData);
            }
        }
    }

    /**
     * Processes Product Contract Data
     *
     * @param string $productCode
     * @param \Magento\Framework\DataObject $erpProduct
     * @param \Magento\Framework\DataObject $uomData
     * @return $this
     */
    protected function processProductContract($productCode, $erpProduct, $uomData)
    {
        $contract = $this->getList()->getContract();
        /* @var $contract Epicor_Lists_Model_Contract */

        $contractProduct = $this->dataObjectFactory->create();
        $contractProduct->setProductSku($productCode);
        $contractProduct->setLineNumber($erpProduct->getContractLineNumber());
        $contractProduct->setPartNumber($erpProduct->getContractPartNumber());
        $contractProduct->setStatus($erpProduct->getLineStatus() == 'A');
        $contractProduct->setStartDate($erpProduct->getEffectiveStartDate());
        $contractProduct->setEndDate($erpProduct->getEffectiveEndDate());

        if ($uomData) {
            $contractProduct->setMinOrderQty($uomData->getMinimumOrderQty());
            $contractProduct->setMaxOrderQty($uomData->getMaximumOrderQty());
            $contractProduct->setIsDiscountable($uomData->getIsDiscountable() == 'Y');
        }

        $contract->addListProducts($contractProduct);

        return $this;
    }

    /**
     * Processes Product Currency Data
     *
     * @param string $productCode
     * @param \Magento\Framework\DataObject $erpCurrency
     * @return $this
     */
    protected function processProductCurrency($productCode, $erpCurrency)
    {
        $currencyCode = $this->commMessagingHelper->create()->getCurrencyMapping($erpCurrency->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $erpPriceBreaks = $this->_getGroupedData('breaks', 'break', $erpCurrency);
        $priceBreaks = array();
        foreach ($erpPriceBreaks as $erpPriceBreak) {
            $priceBreaks[] = array(
                'qty' => $erpPriceBreak->getQuantity(),
                'price' => $erpPriceBreak->getContractPrice(),
                'description' => $erpPriceBreak->getDiscount()->getDescription()
            );
        }

        $erpValueBreaks = $this->_getGroupedData('value_breaks', 'value_break', $erpCurrency);
        $valueBreaks = array();
        foreach ($erpValueBreaks as $erpValueBreak) {
            $valueBreaks[] = array(
                'value' => $erpValueBreak->getLineValue(),
                'price' => $erpValueBreak->getContractPrice(),
                'description' => $erpValueBreak->getDiscount()->getDescription()
            );
        }

        $listProductPrice = $this->dataObjectFactory->create();
        $listProductPrice->setProductId($productCode);
        $listProductPrice->setCurrency($currencyCode);
        $listProductPrice->setPrice($erpCurrency->getContractPrice());
        $listProductPrice->setPriceBreaks($priceBreaks);
        $listProductPrice->setValueBreaks($valueBreaks);

        return $listProductPrice;
    }

    /**
     * Returns Erp Data Accounts
     *
     * @return \Magento\Framework\DataObject $erpAccounts
     */
    protected function getErpAccounts()
    {
        return array($this->getErpData()->getAccountNumber());
    }

    /**
     * Returns the Request Erp Data
     *
     * @return \Magento\Framework\DataObject $erpData
     */
    protected function getErpData()
    {
        if (!$this->_erpData) {
            $this->_erpData = $this->getRequest()->getContract();
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
        $erpCode = $this->getErpData()->getContractCode();
        $erpAccount = $this->getErpData()->getAccountNumber();
        $uomSep = $this->getHelper()->getUOMSeparator();

        return $erpAccount . $uomSep . $erpCode;
    }

    /**
     * Returns Message Products
     *
     * @return \Magento\Framework\DataObject $products
     */
    protected function getProducts()
    {
        if (!$this->_products) {
            $this->_products = $this->_getGroupedData('parts', 'part', $this->getErpData());
        }

        return $this->_products;
    }

    /**
     * updates the branding on the list model
     *
     * 
     */
    protected function setBranding($list, $erpData)
    {
        //       $branding = $erpData->getBrands();
//        if(!$customer->getAccountId())
//            $customer->setAccountId($customer->getAccountNumber());
//        
//        $customer->setOrigAccountNumber($customer->getAccountNumber());
//        $helper = Mage::helper('epicor_comm/messaging_customer');

        $brands = $erpData->getBrands();
        $brand = null;
        if (!is_null($brands))
            $brand = $brands->getBrand();

        if (is_array($brand))
            $brand = $brand[0];

        if (empty($brand) || !$brand->getCompany()) {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End
        }

        $company = $brand->getCompany();

        $list->setBrandCompany($company);
    }

}
