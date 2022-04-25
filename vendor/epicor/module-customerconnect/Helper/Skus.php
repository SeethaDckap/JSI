<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Helper;


class Skus extends \Epicor\Customerconnect\Helper\Data
{

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus\CollectionFactory
     */
    protected $customerconnectResourceErpCustomerSkusCollectionFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuod $customerconnectMessageRequestCuod,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Customer\Skus\CollectionFactory $customerconnectResourceErpCustomerSkusCollectionFactory
    )
    {
        $this->customerconnectResourceErpCustomerSkusCollectionFactory = $customerconnectResourceErpCustomerSkusCollectionFactory;

        parent::__construct($context, $commonLocaleFormatDateHelper, $customerconnectMessageRequestCuod);
    }

    public function getCustomerSkus()
    {

        $commHelper = $this->commHelper;
        $erpAccountInfo = $commHelper->getErpAccountInfo();

        $skus = $this->customerconnectResourceErpCustomerSkusCollectionFactory->create();

        $skus->addFieldToFilter('customer_group_id', $erpAccountInfo->getId());

        $skus->join(
            array('product' => 'catalog_product_entity'), 'main_table.product_id=product.entity_id', array('product.sku' => 'sku')
            //array('name' => 'product_name', 'sku' => 'product_sku')
        );

        return $skus;
    }

    public function canCustomerEditCpns()
    {

        $canCustomerEditCpns = false;

        $cpnEnabledEditing = $this->scopeConfig->getValue('epicor_comm_field_mapping/cpn_mapping/customer_part_enable_editing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $cpnEditingDefault = $this->scopeConfig->getValue('epicor_comm_field_mapping/cpn_mapping/customer_part_editing_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $commHelper = $this->commHelper;
        $erpAccount = $commHelper->getErpAccountInfo();
        $cpnEditingCustomer = $erpAccount->getCpnEditing();
        $defaultErpAccount = $erpAccount->isDefaultForStore();

        if ($cpnEnabledEditing && !$defaultErpAccount) {
            $canCustomerEditCpns = !is_null($cpnEditingCustomer) ? $cpnEditingCustomer : $cpnEditingDefault;
        }

        return $canCustomerEditCpns;
    }

}
