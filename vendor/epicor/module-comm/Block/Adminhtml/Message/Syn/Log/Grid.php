<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Block\Adminhtml\Message\Syn\Log;

/**
 * Description of Grid
 *
 * @author 
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Syn\Log\CollectionFactory
     */
    protected $commResourceSynLogCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;
    
    /**
     * @var \Epicor\Comm\Block\Adminhtml\Message\Syn\Log\Renderer\TypesFactory
     */
    //protected $commAdminhtmlMessageSynLogRendererTypesFactory;

    /**
     * @var \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\TickcrossFactory
     */
    //protected $commonAdminhtmlWidgetGridColumnRendererTickcrossFactory;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Message\Syn\Log\Renderer\UploadedlinkFactory
     */
    //protected $commAdminhtmlMessageSynLogRendererUploadedlinkFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Syn\Log\CollectionFactory $commResourceSynLogCollectionFactory,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
       array $data = []
    )
    {
              
        $this->commResourceSynLogCollectionFactory = $commResourceSynLogCollectionFactory;
        $this->commEntityregHelper = $commEntityregHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('syn_log_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = $this->commResourceSynLogCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {

        $this->addColumn(
            'entity_id', array(
            'header' => __('ID'),
            'align' => 'left',
            'index' => 'entity_id',
            'type' => 'range'
            )
        );
        
        $this->addColumn(
            'types', array(
            'header' => __('Types'),
            'align' => 'left',
            'index' => 'types',
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Syn\Types',
            'filter_condition_callback' => array($this, 'filterType')
            )
        );
        
        $this->addColumn(
            'created_at', array(
            'header' => __('Created At'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'created_at',
            )
        );
        
        $this->addColumn(
            'is_auto', array(
            'header' => __('Is Auto Sync?'),
            'align' => 'center',
            'index' => 'is_auto',
            'width' => '75px',
            'type' => 'options',
            'options' => array(
                '1' => 'Auto',
                '0' => 'Manual'
            ),
           'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross'
            )
        );
        
        $this->addColumn(
            'full_sync', array(
            'header' => __('Full Sync?'),
            'align' => 'center',
            'index' => 'from_date',
            'width' => '75px',
            'type' => 'options',
            'options' => array(
                'yes' => 'Yes',
                'no' => 'No'
            ),
            'tick_mode' => 'empty',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross',
            'filter_condition_callback' => array($this, 'filterIsFull')
            )
        );
        
        $this->addColumn(
            'uploaded', array(
            'header' => __('Uploaded Data'),
            'align' => 'left',
            'index' => 'uploaded',
            'filterable' => false,
            'sortable' => false,
            'renderer' => '\Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Syn\Uploadedlink',
            )
         
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * Filters the is full sync column
     * 
     * @param \Epicor\Common\Model\Message\Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     */
    protected function filterType($collection, $column)
    {
        $filterValue = strtolower($column->getFilter()->getValue());

        $helper = $this->commEntityregHelper;
        /* @var $helper Epicor_Comm_Helper_Entityreg */

        $typeDescs = $helper->getRegistryTypeDescriptions();

        $types = array();

        foreach ($typeDescs as $type => $desc) {
            if (strpos($filterValue, strtolower($desc)) !== false || strpos($filterValue, strtolower($type)) !== false) {
                $types[] = '%' . $type . '%';
            }
        }

        $filterValue = explode(',', $filterValue);

        foreach ($filterValue as $value) {
            foreach ($typeDescs as $type => $desc) {
                if (strpos(strtolower($desc), $value) !== false || strpos(strtolower($type), $value) !== false) {
                    $types[] = '%' . $type . '%';
                }
            }
        }

        $filter = array();

        foreach ($types as $type) {
            $filter[] = array('like' => $type);
        }

        if (!empty($filter)) {
            $collection->addFieldToFilter('types', $filter);
        } else {
            $collection->addFieldToFilter('types', $filterValue);
        }
    }

    /**
     * Filters the is full sync column
     * 
     * @param \Epicor\Common\Model\Message\Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     */
    protected function filterIsFull($collection, $column)
    {
        $filterValue = $column->getFilter()->getValue();
        if ($filterValue == 'yes') {
            $collection->addFieldToFilter('from_date', array('null' => true));
        } else {
            $collection->addFieldToFilter('from_date', array('null' => false));
        }
    }

    /**
     * Filters the is manual/auto column
     * 
     * @param \Epicor\Common\Model\Message\Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     */
    protected function filterManualAuto($collection, $column)
    {
        $filterValue = $column->getFilter()->getValue();
        if ($filterValue == 'yes') {
            $collection->addFieldToFilter('is_auto', array('eq' => '1'));
        } else {
            $collection->addFieldToFilter('is_auto', array('eq' => '0'));
        }
    }
    
    protected function _prepareMassaction()
    {   
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('logid');

        $this->getMassactionBlock()->addItem(
            'delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/deletelog'),
            'confirm' => __('Delete selected SYN log entries?')
            )
        );

        return $this;
        
    } 
    
    public function getRowUrl($row)
    {
        return false;
    }
     
}
