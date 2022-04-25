<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\ResourceModel\Quote;


class Collection extends \Epicor\Database\Model\ResourceModel\Quote\Collection
{
    protected $_idFieldName = 'entity_id';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $eavEntityAttributeFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttributeFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->commHelper = $commHelper;
        $this->eavEntityAttributeFactory = $eavEntityAttributeFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Quotes\Model\Quote','Epicor\Quotes\Model\ResourceModel\Quote');
    }

    /**
     * Joins the Quote Customer table to the select
     * 
     * @return \Epicor_Quotes_Model_Resource_Quote_Collection
     */
    public function joinQuoteCustomerTable()
    {
        $this->getSelect()->joinLeft(
            array(
            'customer' => $this->getTable('ecc_quote_customer')
            ), 'customer.quote_id = main_table.entity_id', array(
            'customer_id' => 'customer_id',
            )
        )->group('main_table.entity_id');

        return $this;
    }

    /**
     * Jonns the ERP Account table to the select
     * 
     * @return \Epicor_Quotes_Model_Resource_Quote_Collection
     */
    public function joinErpAccountTable()
    {
        $this->getSelect()->joinLeft(
            array(
            'erp' => $this->getTable('ecc_erp_account')
            ), 'erp.entity_id = main_table.erp_account_id', array(
            'customer_erp_code' => 'erp_code',
            'customer_company' => 'company',
            'customer_short_code' => 'short_code'
            )
        );

        return $this;
    }

    /**
     * Adds a filter by the customer
     * 
     * @param \Epicor\Comm\Model\Customer $customer
     * 
     * @return \Epicor_Quotes_Model_Resource_Quote_Collection
     */
    public function filterByCustomer($customer)
    {
        $this->addFieldToFilter('currency_code', $this->storeManager->getStore()->getBaseCurrencyCode());

        if ($customer->isCustomer()) {
            $commHelper = $this->commHelper;
            /* @var $commHelper Epicor_Comm_Helper_Data */
            $erpAccountInfo = $commHelper->getErpAccountInfo();
            /* @var $erpAccountInfo Epicor_Comm_Model_Customer_Erpaccount */
            $this->getSelect()
                ->where(
                    'customer.customer_id = ' . $customer->getId()
                    . ' OR (erp_account_id = ' . $erpAccountInfo->getId() . ' AND is_global = 1)'
            );
        } else {
            $this->addFieldToFilter('customer.customer_id', $customer->getId());
        }

        return $this;
    }

    /**
     * Adds Customer information to the select
     * 
     * @return \Epicor_Quotes_Model_Resource_Quote_Collection
     */
    public function addCustomerInfoSelect()
    {
        $firstnameAttr = $this->eavEntityAttributeFactory->create()->loadByCode('1', 'firstname');
        $lastnameAttr = $this->eavEntityAttributeFactory->create()->loadByCode('1', 'lastname');

        $this->getSelect()
            ->joinLeft(
                array('c' => $this->getTable('customer_entity')), 'c.entity_id=customer.customer_id', array('email' => 'email')
            )->joinLeft(
            array('cfirst' => $this->getTable('customer_entity') . '_varchar'), 'cfirst.entity_id=customer.customer_id AND cfirst.attribute_id=' . $firstnameAttr->getAttributeId(), array('firstname' => 'value')
        )->joinLeft(
            array('clast' => $this->getTable('customer_entity') . '_varchar'), 'clast.entity_id=customer.customer_id AND clast.attribute_id=' . $lastnameAttr->getAttributeId(), array('lastname' => 'value')
        )->columns(
            new \Zend_Db_Expr("CONCAT(IFNULL(`cfirst`.`value`,''), ' ',IFNULL(`clast`.`value`,''),' (',`c`.`email`,')') AS customer_info")
        );

        return $this;
    }

    public function getSize()
    {
        return sizeof($this->getAllIds());
    }

}
