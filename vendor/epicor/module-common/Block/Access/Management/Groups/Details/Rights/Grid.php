<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Access\Management\Groups\Details\Rights;


/**
 * Access group rights list grid config 
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
     * @var \Epicor\Common\Model\ResourceModel\Access\Right\CollectionFactory
     */
    protected $commonResourceAccessRightCollectionFactory;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Right\CollectionFactory
     */
    protected $commonResourceAccessGroupRightCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Right\CollectionFactory $commonResourceAccessRightCollectionFactory,
        \Epicor\Common\Model\ResourceModel\Access\Group\Right\CollectionFactory $commonResourceAccessGroupRightCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commonResourceAccessRightCollectionFactory = $commonResourceAccessRightCollectionFactory;
        $this->commonResourceAccessGroupRightCollectionFactory = $commonResourceAccessGroupRightCollectionFactory;
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

        $collection = $this->commonResourceAccessRightCollectionFactory->create();
        /* @var $collection Epicor_Common_Model_Resource_Access_Right_Collection */

        if ($erpAccount->isTypeSupplier()) {
            $collection->addFieldToFilter('type', 'supplier');
        } else if ($erpAccount->isTypeCustomer()) {
            $collection->addFieldToFilter('type', 'customer');
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        if (!$this->registry->registry('access_group_global')) {
            $this->addColumn('entity_id', array(
                'header' => __('Selected'),
                'align' => 'center',
                'index' => 'entity_id',
                'type' => 'checkbox',
                'field_name' => 'rights[]',
                'values' => $this->_getSelectedValues(),
                'sortable' => false
            ));
        }

        $this->addColumn('entity_name', array(
            'header' => __('Access Right'),
            'align' => 'left',
            'index' => 'entity_name'
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

            $collection = $this->commonResourceAccessGroupRightCollectionFactory->create();
            /* @var $collection Epicor_Common_Model_Resource_Access_Group_Right_Collection */

            $collection->addFieldToFilter('group_id', $group->getId());
            foreach ($collection->getItems() as $element) {
                $this->_selected[] = $element->getRightId();
            }
        }

        return $this->_selected;
    }

}
