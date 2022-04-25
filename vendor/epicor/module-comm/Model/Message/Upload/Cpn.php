<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response CPN - Upload Customer Part Number
 * 
 * Specify customer specific alternative part numbers for the given productCode
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Cpn extends \Epicor\Comm\Model\Message\Upload
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    protected $commResourceCustomerSkuCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\SkuFactory
     */
    protected $commCustomerSkuFactory;


    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * Process a request
     *
     * @param array $requestData
     * @return 
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory $commResourceCustomerSkuCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->productFactory = $context->getCatalogProductFactory();
        $this->commResourceCustomerSkuCollectionFactory = $commResourceCustomerSkuCollectionFactory;
        $this->commCustomerSkuFactory = $commCustomerSkuFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/cpn_mapping/');
        $this->setMessageType('CPN');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_customersku', true, true);

    }
    /**
     * Process the CPN action
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest();

        $brands = $this->erpData->getBrands();
        $brand = null;
        if (!is_null($brands))
            $brand = $brands->getBrand();

        if (is_array($brand))
            $brand = $brand[0];

        if (empty($brand) || !$brand->getCompany())
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End

        $company = $brand->getCompany();

        $accountCode = $this->getVarienData('account_number', $this->erpData);

        if (!empty($company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $this->setVarienData('account_number', $this->erpData, $company . $delimiter . $accountCode);
        }

        $delete = $this->getVarienData('delete_flag');
        $customerSku = $this->getVarienData('customer_part_number');
        $customerDescription = $this->getVarienData('customer_part_description');
        $productCode = $this->getVarienData('product_code');
        $accountNumber = $this->getVarienData('account_number');

        $this->setMessageSubject($accountNumber);

        $productId = $this->getProductId($productCode);

        $customerGroupId = $this->getCustomerGroupId($accountNumber, $accountCode);

        $model = $this->findCustomerPartNumber($customerGroupId, $productId, $customerSku);

        if ($delete == 'Y') {
            if ($this->isUpdateable('customer_part_delete_update')) {
                $model->delete();
            }
        } else {
            $model->setCustomerGroupId($customerGroupId);
            $model->setSku($customerSku);
            $model->setProductId($productId);   // product code can't be updated, only added, deleted or description changed 
            if ($this->isUpdateable('customer_part_description_update', !$model->isObjectNew())) {
                $model->setDescription($customerDescription);
            }
            $model->save();
        }
    }

    /**
     * Gets the product Id for the given product code
     * 
     * @param string $productCode
     * @return integer
     * @throws \Exception
     */
    private function getProductId($productCode)
    {
        $this->setMessageSecondarySubject($productCode);
        //M1 > M2 Translation Begin (Rule p2-1)
        //$productId = Mage::getModel('Catalog/Product')->getIdBySku($productCode);
        $productId = $this->productFactory->create()->getIdBySku($productCode);
        //M1 > M2 Translation End
        if ($productId == false) {
            throw new \Exception(
            $this->getErrorDescription(self::STATUS_PRODUCT_NOT_ON_FILE, $productCode), self::STATUS_PRODUCT_NOT_ON_FILE
            );
        }
        return $productId;
    }

    /**
     * Finds the customer group id for the given account number
     * 
     * @param string $accountNumber
     * @return integer
     * @throws \Exception
     */
    private function getCustomerGroupId($accountNumber, $accountCode)
    {
        $customerGroupId = 0;

        $this->setMessageSubject($accountNumber);
        $globalCustomerCode = $this->getConfig('global_customer');
        if ($accountCode != $globalCustomerCode) {
            $customerGroupId = $this->getErpAccount($accountNumber)->getId();
            if (empty($customerGroupId)) {
                throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ACCOUNT_CODE, $accountCode), self::STATUS_INVALID_ACCOUNT_CODE);
            }
        }

        return $customerGroupId;
    }

    /**
     * finds the customer part number for the given params
     * 
     * @param integer $customerGroupId
     * @param integer $productId
     * @param string $customerSku
     * 
     * @return \Epicor\Comm\Model\Erp\Customer\Sku
     */
    private function findCustomerPartNumber($customerGroupId, $productId, $customerSku)
    {

        $collection = $this->commResourceCustomerSkuCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Sku_Collection */
        $collection->addFieldToFilter('customer_group_id', $customerGroupId);
        $collection->addFieldToFilter('product_id', $productId);
        $collection->addFieldToFilter('sku', $customerSku);
        if ($collection->count() == 0) {
            $model = $this->commCustomerSkuFactory->create();
        } else {
            $model = $collection->fetchItem();
        }
        return $model;
    }

}
