<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping\Pac;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended  
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory
     */
    protected $pacAttributeCollectionFactory;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributes\CollectionFactory $pacAttributeCollectionFactory,
        array $data = [])
    {
        $this->pacAttributeCollectionFactory = $pacAttributeCollectionFactory;
       
        parent::__construct($context, $backendHelper, $data);
        $this->setId('pacmappingGrid');
       // $this->setDefaultSort('code');
      //  $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->pacAttributeCollectionFactory->create();
        $collection->getSelect()->joinLeft(
             array('cc'=>$collection->getTable('ecc_pac')),'main_table.class_id = cc.entity_id',
                array('cc_attribute_class_id' => 'cc.attribute_class_id', 'cc_description' => 'cc.description'), null);
        
        $this->setCollection($collection);
        
         return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('cc_attribute_class_id', array(
            'header' => __('Class Id'),
            'align' => 'left',
            'index' => 'cc_attribute_class_id',
            'filter_index' =>'cc.attribute_class_id'
        ));
        $this->addColumn('cc_description', array(
            'header' => __('Class description'),
            'align' => 'left',
           'index' => 'cc_description',
           'filter_index' =>'cc.description' 
        ));
       
        $this->addColumn('attribute_id', array(
            'header' => __('Attribute Id'),
            'align' => 'left',
            'index' => 'attribute_id'
        ));
        
        $this->addColumn('description', array(
            'header' => __('Attribute Description'),
            'align' => 'left',
            'index' => 'description',
            'filter_index' =>'main_table.description' 
        ));
        
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('View'),
                    'url' => array('base' => '*/*/edit' ,'param'=>array('class_id','cc_attribute_class_id')),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'class_id'=>$row->getData('cc_attribute_class_id')));
    }
    
}
