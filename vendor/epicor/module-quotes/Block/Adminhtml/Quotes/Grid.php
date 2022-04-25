<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quotesResourceQuoteCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\TickcrossFactory
     */
    protected $commonAdminhtmlWidgetGridColumnRendererTickcrossFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory $quotesResourceQuoteCollectionFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\TickcrossFactory $commonAdminhtmlWidgetGridColumnRendererTickcrossFactory,
        array $data = []
    )
    {
        $this->commonAdminhtmlWidgetGridColumnRendererTickcrossFactory = $commonAdminhtmlWidgetGridColumnRendererTickcrossFactory;
        $this->quotesResourceQuoteCollectionFactory = $quotesResourceQuoteCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('quotesgrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = $this->quotesResourceQuoteCollectionFactory->create();
        /* @var $collection Epicor_Quotes_Model_Resource_Quote_Collection */

        $collection->joinQuoteCustomerTable()
            ->addCustomerInfoSelect()
            ->joinErpAccountTable()
            ->getSelect()->group('main_table.entity_id');

        $this->setCollection($collection);             
        return parent::_prepareCollection();
    }   
    
    protected function _prepareColumns()
    {

        $this->addColumn(
            'entity_id', array(
            'header' => __('Id'),
            'align' => 'center',
            'index' => 'entity_id',
            'renderer' =>'\Epicor\Quotes\Block\Adminhtml\Quotes\Renderer\Reference',
            //'width' => '70px',            
            'filter_condition_callback' => array($this, '_filterReference')
            )
        );

        $request = $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/gqr_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $upload = $this->scopeConfig->isSetFlag('epicor_comm_field_mapping/gqr_mapping/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($upload || $request) {
            $this->addColumn(
                'quote_number', array(
                'header' => __('ERP Quote Number'),
                'align' => 'center',
                'index' => 'quote_number',
              //  'width' => '70px',
                )
            );
        }

        $this->addColumn(
            'customer_info', array(
            'header' => __('Customer'),
            'index' => 'customer_info',
            'filter_index' => "CONCAT(IFNULL(`cfirst`.`value`,''), ' ',IFNULL(`clast`.`value`,''),' (',`c`.`email`,')')"
            )
        );

        $this->addColumn(
            'customer_erp_code', array(
            'header' => __('ERP Account'),
            'index' => 'customer_short_code',
            'filter_index' => 'erp.short_code'
            )
        );

        $this->addColumn(
            'currency_code', array(
            'header' => __('Currency Code'),
            'index' => 'currency_code',
            )
        );
        
        $this->addColumn(
            'is_global', array(
            'header' => __('Global for ERP Account'),
            'index' => 'is_global',
            'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Tickcross'
            )
        );
        
        $this->addColumn(
            'status', array(
            'header' => __('Status'),
            'index' => 'status_id',
            'type' => 'options',
            'width' => '150px',
            'options' => $this->quotesQuoteFactory->create()->getQuoteStatuses()
            )
        );

        $this->addColumn(
            'expires', array(
            'header' => __('Expires'),
            'index' => 'expires',
            'align' => 'center',
            'type' => 'date',
            'width' => '80px',
            )
        );

        $this->addColumn(
            'created_at', array(
            'header' => __('Created'),
            'index' => 'created_at',
            'align' => 'center',
            'type' => 'date',
            'width' => '80px',
            'filter_index' => 'main_table.created_at',
            )
        );

        $this->addColumn(
            'updated_at', array(
            'header' => __('Last Updated'),
            'align' => 'center',
            'index' => 'updated_at',
            'type' => 'date',
            'width' => '80px',
            'filter_index' => 'main_table.updated_at',
            )
        );



        $this->addColumn(
            'action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

     /**
     * Filter Id by reference or entity id
     */
    protected function _filterReference($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        
        $collection->getSelect()->where(
                "main_table.reference like ? OR main_table.entity_id like ?", 
                "%$value%"
        );
        
        return $this;
    }
}
