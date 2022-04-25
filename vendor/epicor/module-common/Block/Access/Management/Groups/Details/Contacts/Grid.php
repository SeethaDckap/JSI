<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Access\Management\Groups\Details\Contacts;


/**
 * Access management group contact grid config
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{

    protected $_selected;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory
     */
    protected $commonResourceAccessGroupCustomerCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory $commonResourceAccessGroupCustomerCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->customerSession = $customerSession;
        $this->commonResourceAccessGroupCustomerCollectionFactory = $commonResourceAccessGroupCustomerCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setDefaultLimit('all');
    }

    protected function _prepareCollection()
    {
        $erpAccount = $this->registry->registry('access_erp_account');
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */

        $collection->addNameToSelect();
        $collection->addAttributeToSelect('email');
        if ($erpAccount->isTypeSupplier()) {
            $collection->addAttributeToSelect('ecc_supplier_erpaccount_id');
            $collection->addAttributeToFilter('ecc_supplier_erpaccount_id', $erpAccount->getId());
        } else if ($erpAccount->isTypeCustomer()) {
            $collection->addAttributeToSelect('ecc_erpaccount_id');
            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccount->getId());
        }

        $customerId = $this->customerSession->getCustomer()->getId();
        $collection->addFieldToFilter('entity_id', array('neq' => $customerId));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => __('Selected'),
            'align' => 'center',
            'index' => 'entity_id',
            'type' => 'checkbox',
            'field_name' => 'contacts[]',
            'values' => $this->_getSelectedValues(),
            'sortable' => false
        ));

        $this->addColumn('name', array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name'
        ));

        $this->addColumn('email', array(
            'header' => __('Email Address'),
            'align' => 'left',
            'index' => 'email'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    /**
     * Works out which values to select for this grid
     * 
     * @return array
     */
    private function _getSelectedValues()
    {
        if (empty($this->_selected)) {
            $this->_selected = array();

            $group = $this->registry->registry('access_group');
            /* @var $group Epicor_Common_Model_Access_Group */

            $collection = $this->commonResourceAccessGroupCustomerCollectionFactory->create();
            /* @var $collection Epicor_Common_Model_Resource_Access_Group_Customer_Collection */
            $collection->addFieldToFilter('group_id', $group->getId());

            foreach ($collection->getItems() as $element) {
                $this->_selected[] = $element->getCustomerId();
            }
        }

        return $this->_selected;
    }

}
