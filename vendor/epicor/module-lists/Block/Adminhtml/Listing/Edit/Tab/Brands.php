<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


/**
 * List Brands Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Brands extends \Magento\Backend\Block\Widget\Grid\Extended  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Brand\CollectionFactory
     */
    protected $listsResourceListModelBrandCollectionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Brand\CollectionFactory $listsResourceListModelBrandCollectionFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsResourceListModelBrandCollectionFactory = $listsResourceListModelBrandCollectionFactory;
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('brandsGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setRowInitCallback("initListBrand('brands_form','brandsGrid');");
    }

    /**
     * Is this tab shown?
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab Label
     *
     * @return boolean
     */
    public function getTabLabel()
    {
        return 'Brands';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Brands';
    }

    /**
     * Is this tab hidden?
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        if (!isset($this->list)) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->list;
    }

    /**
     * Build data for List Brands
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Brands
     */
    protected function _prepareCollection()
    {
        $collection = $this->listsResourceListModelBrandCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_Listing_Brand_Collection */
        $collection->addFieldToFilter('list_id', $this->getList()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for List Brands
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Brands
     */
    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        $this->addColumn('company', array(
            'header' => __('Company'),
            'index' => 'company',
            'type' => 'text'
        ));

        $this->addColumn('site', array(
            'header' => __('Site'),
            'index' => 'site',
            'type' => 'text'
        ));

        $this->addColumn('warehouse', array(
            'header' => __('Warehouse'),
            'index' => 'warehouse',
            'type' => 'text'
        ));

        $this->addColumn('group', array(
            'header' => __('Group'),
            'index' => 'group',
            'filter_index' => 'main_table.group',
            'type' => 'text'
        ));

        $this->addColumn('actions', array(
            'header' => __('Actions'),
            'width' => '100',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'onclick' => 'javascript: listBrand.rowEdit(this)',
                ),
                array(
                    'caption' => __('Delete'),
                    'onclick' => 'javascript: if(window.confirm(\''
                    . addslashes($this->escapeHtml(__('Are you sure you want to do this?')))
                    . '\')){listBrand.rowDelete(this);} return false;',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
        ));

        $this->addColumn('rowdata', array(
            'header' => __(''),
            'align' => 'left',
            'width' => '1',
            'name' => 'rowdata',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Rowdata',
            'column_css_class' => 'no-display last',
            'header_css_class' => 'no-display last',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Used in grid to return selected Brands values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected Brands
     * 
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */

            foreach ($list->getBrands() as $brand) {
                $this->_selected[$brand->getId()] = array('id' => $brand->getId());
            }
        }
        return $this->_selected;
    }

    /**
     * Sets the selected items array
     *
     * @param array $selected
     *
     * @return void
     */
    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array('id' => $id);
            }
        }
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params = array(
            'id' => $this->getList()->getId(),
            '_current' => true,
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/brandsgrid', $params);
    }

    /**
     * Row Click URL
     *
     * @param Mage_Core_Model_Brand_Group $row
     * 
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Add'),
                    'onclick' => "listBrand.add();",
                    'class' => 'task'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return \Magento\Backend\Block\Widget\Grid
     */
//    protected function _setCollectionOrder($column)
//    {
//        $collection = $this->getCollection();
//        if ($collection && $column->getIndex() == 'group') {
//            $collection->setOrder('`group`', strtoupper($column->getDir()));
//        } else {
//            parent::_setCollectionOrder($column);
//        }
//        
//        return $this;
//    }
}
