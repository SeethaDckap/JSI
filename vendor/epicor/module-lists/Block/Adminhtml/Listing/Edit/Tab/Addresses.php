<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab;


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
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory $listsResourceListModelAddressCollectionFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('addressesGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        if ($this->isSectionEditable()) {
            $this->setRowInitCallback("initListAddress('addresses_form','addressesGrid');");
        }
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
        return 'Addresses';
    }

    /**
     * Tab Title
     *
     * @return boolean
     */
    public function getTabTitle()
    {
        return 'Addresses';
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
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!$this->list) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->list;
    }

    /**
     * Build data for List Addresses
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Addresses
     */
    protected function _prepareCollection()
    {
        $collection = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_Listing_Address_Collection */
        $collection->addFieldToFilter('list_id', $this->getList()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Build columns for List Addresses
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Addresses
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'address_code', array(
            'header' => __('Address Code'),
            'index' => 'address_code',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'purchase_order_number', array(
            'header' => __('Purchase Order Number'),
            'index' => 'purchase_order_number',
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

        $this->addColumn(
            'address_email_address', array(
            'header' => __('Email'),
            'index' => 'email_address',
            'type' => 'text'
            )
        );

        if ($this->getList()->getType() == 'Co') {
            $this->addColumn(
                'activation_date', array(
                'header' => __('Activation Date'),
                'index' => 'activation_date',
                'type' => 'datetime'
                )
            );

            $this->addColumn(
                'expiry_date', array(
                'header' => __('Expiry Date'),
                'index' => 'expiry_date',
                'type' => 'datetime'
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
                        'onclick' => 'javascript: listAddress.rowEdit(this)',
                    ),
                    array(
                        'caption' => __('Delete'),
                        'onclick' => 'javascript: if(window.confirm(\''
                        . addslashes($this->escapeHtml(__('Are you sure you want to do this?')))
                        . '\')){listAddress.rowDelete(this);} return false;',
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
        }

        return parent::_prepareColumns();
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
            'id' => $this->getList()->getId(),
            '_current' => true,
        );
        return $this->getUrl('epicor_lists/epicorlists_lists/addressesgrid', $params);
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
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    protected function _prepareLayout()
    {
        if ($this->isSectionEditable()) {
            $this->setChild(
                'add_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => __('Add'),
                        'onclick' => "listAddress.add();",
                        'class' => 'task'
                    ))
            );
        }
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml()
    {
        $html = $this->isSectionEditable() ? $this->getAddButtonHtml() : '';
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    public function isSectionEditable()
    {
        return $this->getList()->getTypeInstance()->isSectionEditable('addresses');
    }

}
