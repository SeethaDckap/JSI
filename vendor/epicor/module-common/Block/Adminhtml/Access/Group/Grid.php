<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Group;


class Grid extends \Magento\Backend\Block\Widget\Grid
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory
     */
    protected $commonResourceAccessGroupCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\ResourceModel\Access\Group\CollectionFactory $commonResourceAccessGroupCollectionFactory,
        array $data = []
    )
    {
        $this->commonResourceAccessGroupCollectionFactory = $commonResourceAccessGroupCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('accessGroupGrid');
        $this->setDefaultSort('entity_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commonResourceAccessGroupCollectionFactory->create();
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
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
