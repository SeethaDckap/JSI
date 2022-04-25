<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Adminhtml\Listing;

use Epicor\Lists\Model\ImportFactory as ImportFactory;
use Epicor\Lists\Model\Import;
use Epicor\Lists\Model\ResourceModel\Import\CollectionFactory as CollectionFactory;

/**
 * Adminhtml mass Csv upload block
 *
 * @api
 * @since 100.0.2
 */
class MassGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var null|Import
     */
    private $import;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * MassGrid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param CollectionFactory                       $collectionFactory
     * @param ImportFactory                           $importFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $collectionFactory,
        ImportFactory $importFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->importFactory = $importFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('massGrid');
        $this->setDefaultSort('id');
        $this->setSaveParametersInSession(false);
        $this->setDefaultDir('DESC');
        $this->setEmptyText(__('No Record Found.'));
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params = array(
            'id'       => $this->getImport()->getId(),
            '_current' => true,
        );

        return $this->getUrl('*/*/addbycsv', $params);
    }

    /**
     * @return null
     */
    public function getImport()
    {
        if (!isset($this->import)) {
            $this->import = $this->importFactory->create()->load(
                $this->getRequest()->getParam('id')
            );
        }

        return $this->import;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Epicor\Lists\Model\ResourceModel\Import\Collection */
        $collection = $this->collectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'       => __('Log ID'),
            'align'        => 'left',
            'index'        => 'id',
            'filter_index' => 'main_table.id',
        ));

        $this->addColumn(
            'file_name',
            array(
                'header' => __('File Name'),
                'index'  => 'file_name',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'index'  => 'status',
                'type'   => 'text',
            )
        );

        $this->addColumn(
            'created_at',
            array(
                'header' => __('Created At'),
                'align'  => 'left',
                'type'   => 'datetime',
                'index'  => 'created_at',
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'    => __('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
                'links' => 'true',
                'actions'   => array(
                    array(
                        'caption' => __('View'),
                        'url'     => array('base' => '*/*/view'),
                        'field'   => 'id',
                    ),
                    array(
                        'caption' => __('Delete'),
                        'url' => array('base' => '*/*/massLogDelete'),
                        'field' => 'id',
                        'confirm' => __('Are you sure you want to delete this Log? This cannot be undone')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Mass Action.
     *
     * @return $this|MassGrid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('massid');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massLogDelete'),
            'confirm' => __('Delete Selected Import Log?')
        ));

        return $this;
    }
}
