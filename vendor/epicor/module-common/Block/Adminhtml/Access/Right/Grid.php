<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right;


class Grid extends \Magento\Backend\Block\Widget\Grid
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Right\CollectionFactory
     */
    protected $commonResourceAccessRightCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\ResourceModel\Access\Right\CollectionFactory $commonResourceAccessRightCollectionFactory,
        array $data = []
    )
    {
        $this->commonResourceAccessRightCollectionFactory = $commonResourceAccessRightCollectionFactory;
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
        $collection = $this->commonResourceAccessRightCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_name', array(
            'header' => __('Access Right'),
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
