<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Access\Management\Groups\Listing;


/**
 * Customer access groups grid 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory
     */
    protected $commonResourceAccessGroupCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory $commonResourceAccessGroupCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commonResourceAccessGroupCollectionFactory = $commonResourceAccessGroupCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    protected function _prepareCollection()
    {
        $erpAccount = $this->registry->registry('access_erp_account');
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $collection = $this->commonResourceAccessGroupCollectionFactory->create();
        /* @var $collection Epicor_Common_Model_Resource_Access_Group_Collection */
        $collection->addFieldToFilter(
            'erp_account_id', array(
            array('eq' => $erpAccount->getId()),
            array('null' => '')
            )
        );

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
        $this->addColumn('entity_name', array(
            'header' => __('Access Group'),
            'align' => 'left',
            'index' => 'entity_name'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/editgroup', array('id' => $row->getId()));
    }

}
