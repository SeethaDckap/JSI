<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Advanced\Entity\Register\Renderer;


/**
 * Entity register log details renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Details extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

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
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Model\Customer\SkuFactory $commCustomerSkuFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
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
            $data
        );
    }


    /**
     * Render column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $function = 'get' . $row->getType() . 'Details';
        return $this->$function($row);
    }

    private function getErpAccountDetails($row)
    {
        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($row->getEntityId());
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Short Code: ' . $erpAccount->getShortCode();
        }

        return $details;
    }

    private function getErpAddressDetails($row)
    {
        $erpAddress = $this->commCustomerErpaccountAddressFactory->create()->load($row->getEntityId());
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount_Address */

        if ($erpAddress->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'ERP Code: ' . $erpAddress->getErpCode();
        }

        return $details;
    }

    private function getRelatedDetails($row)
    {
        return $this->getAlternativeDetails($row, 'related');
    }

    private function getUpSellDetails($row)
    {
        return $this->getAlternativeDetails($row, 'upsell');
    }

    private function getCrossSellDetails($row)
    {
        return $this->getAlternativeDetails($row, 'cross sell');
    }

    private function getAlternativeDetails($row, $type)
    {
        $entity = $this->catalogProductFactory->create()->load($row->getEntityId());
        /* @var $entity Epicor_Comm_Model_Product */

        $child = $this->catalogProductFactory->create()->load($row->getChildId());
        /* @var $child Epicor_Comm_Model_Product */

        if ($entity->isObjectNew() || $child->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'SKU ' . $entity->getSku() . ': ' . $type . ' link to sku ' . $child->getSku();
        }

        return $details;
    }

    private function getCustomerSkuDetails($row)
    {
        $entity = $this->commCustomerSkuFactory->create()->load($row->getEntityId());
        /* @var $entity Epicor_Comm_Model_Customer_Sku */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Customer SKU ' . $entity->getSku();
        }

        return $details;
    }

    private function getCategoryProductDetails($row)
    {
        $entity = $this->catalogProductFactory->create()->load($row->getEntityId());
        /* @var $entity Epicor_Comm_Model_Product */

        $child = $this->catalogCategoryFactory->create()->load($row->getChildId());
        /* @var $child Epicor_Comm_Model_Category */

        if ($entity->isObjectNew() || $child->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Product ' . $entity->getSku() . ' link to category ' . $child->getName();
        }

        return $details;
    }

    private function getCategoryDetails($row)
    {
        $entity = $this->catalogCategoryFactory->create()->load($row->getEntityId());
        /* @var $child Epicor_Comm_Model_Category */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Category ' . $entity->getName();
        }

        return $details;
    }

    private function getProductDetails($row)
    {
        $entity = $this->catalogProductFactory->create()->load($row->getEntityId());
        /* @var $entity Epicor_Comm_Model_Product */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Product ' . $entity->getSku();
        }

        return $details;
    }

    private function getCustomerDetails($row)
    {
        $entity = $this->customerCustomerFactory->create()->load($row->getEntityId());
        /* @var $entity Mage_Customer_Model_Customer */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Customer ' . $entity->getEmail();
        }

        return $details;
    }

    private function getSupplierDetails($row)
    {
        $entity = $this->customerCustomerFactory->create()->load($row->getEntityId());
        /* @var $entity Mage_Customer_Model_Customer */

        if ($entity->isObjectNew()) {
            $details = 'No longer available';
        } else {
            $details = 'Supplier ' . $entity->getEmail();
        }

        return $details;
    }

}
