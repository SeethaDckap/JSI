<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Entity;


/**
 * Entity Register Model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method integer getRowId()
 * @method string getType()
 * @method string getDetails()
 * @method integer getEntityId()
 * @method integer getChildId()
 * @method datetime getCreatedAt()
 * @method datetime getModifiedAt()
 * @method datetime getToBeDeleted()
 * @method boolean getIsDirty()
 * 
 * @method setType(string $type)
 * @method setDetails(string $type)
 * @method setEntityId(integer $id)
 * @method setChildId(integer $id)
 * @method setCreatedAt(datetime $id)
 * @method setModifiedAt(datetime $id)
 * @method setToBeDeleted(boolean $toBeDeleted)
 * @method setIsDirty(boolean $isDirty)
 * 
 */
class Register extends \Epicor\Database\Model\Entity\Register
{

    protected $_eventPrefix = 'ecc_entity_register';
    protected $_eventObject = 'entity_register';

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\SkuFactory
     */
    protected $commCustomerSkuFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->commCustomerSkuFactory = $commCustomerSkuFactory;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    protected function _construct()
    {
        $this->_init('Epicor\Comm\Model\ResourceModel\Entity\Register');
    }

    public function beforeSave()
    {
        $this->_processDetails();
        parent::beforeSave();
    }

    private function _processDetails()
    {
        $function = 'get' . $this->getType() . 'Details';
        $this->setDetails($this->$function());
    }

    private function getErpAccountDetails()
    {
        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($this->getEntityId());
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Short Code: ' . $erpAccount->getShortCode();
        }

        return $details;
    }

    private function getSupplierErpAccountDetails()
    {
        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($this->getEntityId());
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Short Code: ' . $erpAccount->getShortCode();
        }

        return $details;
    }

    private function getErpAddressDetails()
    {
        $erpAddress = $this->commCustomerErpaccountAddressFactory->create()->load($this->getEntityId());
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount\Address */

        if ($erpAddress->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'ERP Code: ' . $erpAddress->getErpCode();
        }

        return $details;
    }

    private function getRelatedDetails()
    {
        return $this->getAlternativeDetails('related');
    }

    private function getUpSellDetails()
    {
        return $this->getAlternativeDetails('upsell');
    }

    private function getCrossSellDetails()
    {
        return $this->getAlternativeDetails('cross sell');
    }

    private function getAlternativeDetails($type)
    {
        $entity = $this->catalogProductFactory->create()->load($this->getEntityId());
        /* @var $entity \Epicor\Comm\Model\Product */

        $child = $this->catalogProductFactory->create()->load($this->getChildId());
        /* @var $child \Epicor\Comm\Model\Product */

        if ($entity->isObjectNew() || $child->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'SKU ' . $entity->getSku() . ': ' . $type . ' link to sku ' . $child->getSku();
        }

        return $details;
    }

    private function getCustomerSkuDetails()
    {
        $entity = $this->commCustomerSkuFactory->create()->load($this->getEntityId());
        /* @var $entity \Epicor\Comm\Model\Customer\Sku */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Customer SKU ' . $entity->getSku();
        }

        return $details;
    }

    private function getCategoryProductDetails()
    {
        $entity = $this->catalogProductFactory->create()->load($this->getEntityId());
        /* @var $entity \Epicor\Comm\Model\Product */

        $child = $this->catalogCategoryFactory->create()->load($this->getChildId());
        /* @var $child \Epicor\Comm\Model\Category */

        if ($entity->isObjectNew() || $child->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Product ' . $entity->getSku() . ' link to category ' . $child->getName();
        }

        return $details;
    }

    private function getCategoryDetails()
    {
        $entity = $this->catalogCategoryFactory->create()->load($this->getEntityId());
        /* @var $child \Epicor\Comm\Model\Category */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Category ' . $entity->getName();
        }

        return $details;
    }

    private function getProductDetails()
    {
        $entity = $this->catalogProductFactory->create()->load($this->getEntityId());
        /* @var $entity \Epicor\Comm\Model\Product */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Product ' . $entity->getSku();
        }

        return $details;
    }

    private function getCustomerDetails()
    {
        $entity = $this->customerCustomerFactory->create()->load($this->getEntityId());
        /* @var $entity \Magento\Customer\Model\Customer */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Customer ' . $entity->getEmail();
        }

        return $details;
    }

    private function getSupplierDetails()
    {
        $entity = $this->customerCustomerFactory->create()->load($this->getEntityId());
        /* @var $entity \Magento\Customer\Model\Customer */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Supplier ' . $entity->getEmail();
        }

        return $details;
    }

}
