<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block\Adminhtml\Faqs;


/**
 * F.A.Q. adminhtml edition grid
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 *
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{


    /**
     * @var \Epicor\Faqs\Model\FaqsFactory
     */
    protected $faqsFaqsFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeCollectionFactory;
    /**
     * Init Grid default properties
     *
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Faqs\Model\FaqsFactory $faqsFaqsFactory,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        array $data = [])
    {
        $this->faqsFaqsFactory=$faqsFaqsFactory;
        $this->storeCollectionFactory=$storeCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('faqsGrid');
        if ($this->_scopeConfig->getValue('epicor_faqs/view/sort') == 'usefulness') {
            $this->setDefaultSort('usefulness');
            $this->setDefaultDir('DSC');
        } else {
            $this->setDefaultSort('weight');
            $this->setDefaultDir('ASC');
        }

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    /**
     * Prepare collection for Grid
     *
     * @return \Epicor\Faqs\Block\Adminhtml\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->faqsFaqsFactory->create()->getResourceCollection()
            ->addExpressionFieldToSelect('usefulness', '(useful-useless)', array('useful' => 'useful', 'useless' => 'useless'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Grid
     */
    protected function _prepareColumns()
    {
        //Id column 
        $this->addColumn('faqs_id', array(
            'header' => __('ID'),
            'width' => '50px',
            'index' => 'faqs_id',
        ));
        //Weight column for sorting
        $this->addColumn('weight', array(
            'header' => __('Weight'),
            'index' => 'weight',
        ));
        //Usefulness=upvotes-downvotes
        $this->addColumn('usefulness', array(
            'header' => __('Usefulness'),
            'index' => 'usefulness',
        ));
        //Question column
        $this->addColumn('question', array(
            'header' => __('Question'),
            'width' => '320px',
            'index' => 'question',
        ));
        //Answer column
        $this->addColumn('answer', array(
            'header' => __('Answer'),
            'index' => 'answer',
        ));
        //Keywords column
        $this->addColumn('keywords', array(
            'header' => __('Keywords'),
            'index' => 'keywords',
        ));
        //Stores list
        $this->addColumn('stores', array(
            'header' => __('Stores'),
            'index' => 'stores',
            'width' => '170px',
            'renderer' => 'Epicor\Faqs\Block\Adminhtml\Faqs\Column\Renderer\Stores',
            'filter_condition_callback' => array($this, 'filterStores')
        ));
        //Creation timestamp
        $this->addColumn('created_at', array(
            'header' => __('Created'),
            'sortable' => true,
            'width' => '170px',
            'index' => 'created_at',
            'type' => 'datetime',
        ));
        //Edition link column
        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'faqs',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Return row URL for js event handlers
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Grid url getter
     *
     * @return string current grid url
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Custom filter to store column
     *
     * @param $collection Epicor_Faqs_Model_Resource_Faqs_Collection
     * @param $column
     * @return void
     */
    protected function filterStores($collection, $column)
    {
        /* @var $store_collection Mage_Core_Model_Resource_Store_Collection */
        //M1 > M2 Translation Begin (Rule p2-1)
        //$store_collection = Mage::getModel('core/store')->addFieldToFilter('name', array('like' => '%' . $column->getFilter()->getValue() . '%'));
        $store_collection = $this->storeCollectionFactory->create()->addFieldToFilter('name', array('like' => '%' . $column->getFilter()->getValue() . '%'));
        //M1 > M2 Translation End
        $store_filter = array();
        foreach ($store_collection as $store) {
            $store_filter[] = array('finset' => $store->getId());
        }
        $store_filter = empty($store_filter) ? array('eq' => false) : $store_filter;

        $collection->addFieldToFilter('stores', $store_filter);
    }

}
