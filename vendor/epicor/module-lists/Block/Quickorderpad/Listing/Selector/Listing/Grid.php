<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Quickorderpad\Listing\Selector\Listing;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        array $data = []
    )
    {
        $this->listsQopHelper = $listsQopHelper;
        $this->storeManager = $context->getStoreManager();
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('quickorderpad_list_selector_list');
        $this->setIdColumn('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setRowClickCallback('populateListsSelect');
        $this->setCacheDisabled(true);

        $lists = $this->listsQopHelper->getQuickOrderPadLists();

        $store = $this->storeManager->getStore();
        foreach ($lists as $list) {
            /* @var $list Epicor_Lists_Model_ListModel */
            $label = $list->getStoreLabel($store);
            $list->setLabel($label);
        }

        $this->setCustomData($lists);
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml(false);
        return $html;
    }

    protected function _getColumns()
    {
        $columns = array(
            'title' => array(
                'header' => __('Title'),
                'align' => 'left',
                'index' => 'title',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'type' => array(
                'header' => __('Type'),
                'align' => 'center',
                'index' => 'type',
                'type' => 'options',
                'width' => 150,
                'options' => $this->listsListModelTypeFactory->create()->toQopFilterArray()
            ),
            'product_count' => array(
                'header' => __('Product Count'),
                'align' => 'center',
                'index' => 'product_count',
                'type' => 'number',
                'width' => 50,
            ),
            'entity_id' => array(
                'header' => __('id'),
                'align' => 'left',
                'index' => 'id',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
                'type' => 'hidden',
                'condition' => 'LIKE'
            ),
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

}
