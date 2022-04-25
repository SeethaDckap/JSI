<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Advanced\Entity\Register;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Entity\Register\CollectionFactory
     */
    protected $commResourceEntityRegisterCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Advanced\Entity\Register\Renderer\TypeFactory
     */
    protected $commAdminhtmlAdvancedEntityRegisterRendererTypeFactory;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Entity\Register\CollectionFactory $commResourceEntityRegisterCollectionFactory,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Epicor\Comm\Block\Adminhtml\Advanced\Entity\Register\Renderer\TypeFactory $commAdminhtmlAdvancedEntityRegisterRendererTypeFactory,
        array $data = []
    )
    {
        $this->commAdminhtmlAdvancedEntityRegisterRendererTypeFactory = $commAdminhtmlAdvancedEntityRegisterRendererTypeFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commResourceEntityRegisterCollectionFactory = $commResourceEntityRegisterCollectionFactory;
        $this->commEntityregHelper = $commEntityregHelper;
        
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        
        $this->setId('entity_register_grid');
        $this->setDefaultSort('row_id');
        $this->setDefaultDir('desc');
        
        $this->setDefaultFilter(
            array(
                'is_dirty' => 1,
                'type' => $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/uploaded_data_filter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            )
        ); 

        $this->setSaveParametersInSession(false);
        
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceEntityRegisterCollectionFactory->create();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
           
        $this->addColumn(
            'type', array(
            'header' => __('Type'),
            'align' => 'left',
            'index' => 'type',
            'renderer' =>'Epicor\Comm\Block\Adminhtml\Advanced\Entity\Register\Renderer\Type',
            'width' => '200px',
            'filter_condition_callback' => array($this, 'filterType')
            )
        ); 

        $this->addColumn(
            'details', array(
            'header' => __('Entity Details'),
            'align' => 'left',
            'index' => 'details',
            'width' => '700px'
            )
        );
        
        $this->addColumn(
            'is_dirty', array(
            'header' => __('Mismatch'),
            'align' => 'left',
            'type' => 'options',
            'options' => array(
                '1' => 'Yes',
                '0' => 'No'
            ),
            'index' => 'is_dirty',
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
            'modified_at', array(
            'header' => __('Modified At'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'modified_at',
            'filter_time' => true
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
                $types[] = $type;
            }
        }

        $filterValue = explode(',', $filterValue);

        foreach ($filterValue as $value) {
            foreach ($typeDescs as $type => $desc) {
                if (strpos(strtolower($desc), $value) !== false || strpos(strtolower($type), $value) !== false) {
                    $types[] = $type;
                }
            }
        }

        $inNin = $helper->getRegistryTypes($types);

        $collection->addFieldToFilter($column->getId(), array('in' => array_unique($inNin)));
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('row_id');
        $this->getMassactionBlock()->setFormFieldName('rowid');

        $this->getMassactionBlock()->addItem(
            'mark_for_deletion', array(
            'label' => __('Mark For Deletion'),
            'url' => $this->getUrl('*/*/markForDeletion'),
            'confirm' => __('Delete selected items?')
            )
        );
    
        return $this;
    } 

    public function getRowUrl($row)
    {
        return false;
    }

}
