<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions;


/**
 * List Addresses Serialized Grid
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Addresses extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    private $_selected = array();

    /**
     * @var \Epicor\Lists\Model\ListModel
     */
    protected $list;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Helper\Data $listsHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->backendAuthSession = $backendAuthSession;
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('restrictedaddressGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setCacheDisabled(true);
        $this->setFilterVisibility(true);
        $this->setDefaultFilter(array('selected_addresses' => 1));
        $this->setRowInitCallback("initListRestrictionAddress('restrictions_form','restrictedaddressGrid');");
    }

    /**
     * Gets the List for this tab
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!$this->list) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('list_id'));
            }
        }
        return $this->list;
    }

    /**
     * Build data for List Restricted Addresses
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions\Addresses
     */
    protected function _prepareCollection()
    {
        $restrictionType = $this->backendAuthSession->getRestrictionTypeValue();

        $collection = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_Listing_Address_Collection */
        $collection->addFieldToFilter('main_table.list_id', $this->getList()->getId());

        $restrictionTable = $collection->getTable('ecc_list_address_restriction');
        $collection->getSelect()->join(array('r' => $restrictionTable), 'r.address_id = main_table.id', array());
        $collection->addFieldToFilter('r.restriction_type', $restrictionType);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Build columns for List restricted Addresses
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Addresses
     */
    protected function _prepareColumns()
    {
        $restrictionType = $this->backendAuthSession->getRestrictionTypeValue();
        $id = $this->getList()->getId();

        if ($restrictionType == \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ADDRESS) {
            $this->addColumn(
                'address_code', array(
                'header' => __('Address Code'),
                'index' => 'address_code',
                'type' => 'text'
                )
            );

            $this->addColumn(
                'address_name', array(
                'header' => __('Name'),
                'index' => 'name',
                'type' => 'text'
                )
            );

            $this->addColumn(
                'flatt_address', array(
                'header' => __('Address'),
                'index' => 'address1',
                'type' => 'text',
                'renderer' => 'Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Listing\Address',
                'filter_condition_callback' => array($this, '_addressFilter'),
                )
            );
        }

        $this->addColumn(
            'country', array(
            'header' => __('Country'),
            'index' => 'country',
            'type' => 'country',
            'width' => '200px'
            )
        );

        if ($restrictionType == \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_STATE) {
            $this->addColumn(
                'county', array(
                'header' => __('County'),
                'index' => 'county',
                'type' => 'text'
                )
            );
        }
        
        if ($restrictionType == \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ZIP) {
            $this->addColumn(
                'postcode', array(
                'header' => __('Postcode'),
                'index' => 'postcode',
                'type' => 'text',
                'renderer' => 'Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions\Renderer\Postcode',
                )
            );
        }

        if ($this->isSectionEditable()) {
            $this->addColumn('actions', array(
                'header' => __('Actions'),
                'width' => '100',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                    ),
                    array(
                        'caption' => __('Delete'),
                        'onclick' => 'javascript: if(window.confirm(\''
                        . addslashes($this->escapeHtml(__('Are you sure you want to do this?')))
                        . '\')){listRestrictionAddress.rowDelete(this);} return false;',
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'id',
                'is_system' => true,
                'renderer' => 'Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions\Renderer\Action',
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
        }
        return parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_addresses') {
            $ids = $this->_getSelected();
            if (empty($ids)) {
                $ids = array(0);
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.id', array('in' => $ids));
            } else if ($ids) {
                $this->getCollection()->addFieldToFilter('main_table.id', array('nin' => $ids));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Used in grid to return selected Customers values.
     * 
     * @return array
     */
    protected function _getSelected()
    {
        return array_keys($this->getSelected());
    }

    /**
     * Builds the array of selected Customers
     * 
     * @return array
     */
    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $list = $this->getList();
            /* @var $list Epicor_Lists_Model_ListModel */

            foreach ($list->getAddresses() as $address) {
                $this->_selected[$address->getId()] = array('id' => $address->getId());
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

    protected function _addressFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $clone = clone $collection;

        $filterIds = array();
        foreach ($clone->getItems() as $item) {
            /* @var $item Epicor_Lists_Model_ListModel */
            if (stripos($item->getFlattenedAddress(), $value) !== false) {
                $filterIds[] = $item->getId();
            }
        }

        $collection->addFieldToFilter('id', array('in' => $filterIds));
    }

    /**
     * Gets grid url for ajax reloading
     *
     * @return string
     */
    public function getGridUrl()
    {
        $params = array(
            'list_id' => $this->getList()->getId(),
            '_current' => true,
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/restrictionsgrid', $params);
    }

    /**
     * Row Click URL
     *
     * @param \Epicor\Lists\Model\ListModel\Address $row
     * 
     * @return null
     */
    public function getRowUrl($row)
    {
        return null;
        //  return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    protected function _prepareLayout()
    {
        $restrictionType = $this->backendAuthSession->getRestrictionTypeValue();
        $id = $this->getList()->getId();

        if ($this->isSectionEditable()) {
            $this->setChild(
                'add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setData(array(
                        'label' => __('Add'),
                        'onclick' => "openRestrictionForm('',$id,'add','" . $restrictionType . "');",
                        'class' => 'task'
                    ))
            );
        }
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    public function isSectionEditable()
    {
        return 1;
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
        return 'Restrictions';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Restrictions';
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

}
