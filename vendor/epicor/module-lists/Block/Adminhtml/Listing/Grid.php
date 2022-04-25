<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing;


/**
 * List admin actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    const LIST_STATUS_ACTIVE = 'A';
    const LIST_STATUS_DISABLED = 'D';
    const LIST_STATUS_ENDED = 'E';
    const LIST_STATUS_PENDING = 'P';

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModel\TypeFactory
     */
    protected $listsListModelTypeFactory;

    /**
     * @var \Epicor\Lists\Block\Adminhtml\Listing\Grid\Renderer\ErpcodeFactory
     */
    protected $listsAdminhtmlListingGridRendererErpcodeFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Epicor\Lists\Model\ListModel\TypeFactory $listsListModelTypeFactory,
        \Epicor\Lists\Block\Adminhtml\Listing\Grid\Renderer\ErpcodeFactory $listsAdminhtmlListingGridRendererErpcodeFactory,
        array $data = []
    )
    {
        $this->listsAdminhtmlListingGridRendererErpcodeFactory = $listsAdminhtmlListingGridRendererErpcodeFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->listsHelper = $listsHelper;
        $this->listsListModelTypeFactory = $listsListModelTypeFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('list_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->listsResourceListModelCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */


        $this->addColumn(
            'id', array(
            'header' => __('ID'),
            'index' => 'id',
            'type' => 'number'
            )
        );

        $typeModel = $this->listsListModelTypeFactory->create();
        /* @var $typeModel Epicor_Lists_Model_ListModel_Type */

        $this->addColumn(
            'type', array(
            'header' => __('Type'),
            'index' => 'type',
            'type' => 'options',
            'options' => $typeModel->toFilterArray()
            )
        );

        $this->addColumn(
            'title', array(
            'header' => __('Title'),
            'index' => 'title',
            'type' => 'text'
            )
        );

        $this->addColumn(
            'erp_code', array(
            'header' => __('ERP Code'),
            'index' => 'erp_code',
            'type' => 'text',
            'renderer' => '\Epicor\Lists\Block\Adminhtml\Listing\Grid\Renderer\Erpcode'   
            )
        );

        $this->addColumn(
            'start_date', array(
            'header' => __('Start Date'),
            'index' => 'start_date',
            'type' => 'datetime'
            )
        );

        $this->addColumn(
            'end_date', array(
            'header' => __('End Date'),
            'index' => 'end_date',
            'type' => 'datetime'
            )
        );

        $this->addColumn(
            'active', array(
            'header' => __('Active'),
            'index' => 'active',
            'type' => 'options',
            'options' => array(
                0 => __('No'),
                1 => __('Yes')
            )
            )
        );

        $this->addColumn(
            'status', array(
            'header' => __('Current Status'),
            'index' => 'active',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'renderer' => '\Epicor\Lists\Block\Adminhtml\Widget\Grid\Column\Renderer\Active',
            'type' => 'options',
            'options' => array(
                self::LIST_STATUS_ACTIVE => __('Active'),
                self::LIST_STATUS_DISABLED => __('Disabled'),
                self::LIST_STATUS_ENDED => __('Ended'),
                self::LIST_STATUS_PENDING => __('Pending')
            ),
            'filter_condition_callback' => array($this, '_statusFilter'),
            )
        );

        $this->addColumn(
            'source', array(
            'header' => __('Source'),
            'index' => 'source',
            'type' => 'text'
            )
        );

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'renderer' => '\Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer\Action',
            'links' => 'true',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => __('Are you sure you want to delete this List? This cannot be undone')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('listid');
        $helper = $this->listsHelper;

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected Lists?')
        ));

        $this->getMassactionBlock()->addItem('assignerpaccount', array(
            'label' => __('Assign ERP Account'),
            'url' => $this->getUrl('*/*/massAssignErpAccount'),
            'additional' => array(
                'ecc_erp_account_type' => array(
                    'name' => 'assign_erp_account',
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpaccount',
                    'renderer' => array(
                        'type' => 'account_selector',
                        'class' => 'Epicor_Comm_Block_Adminhtml_Form_Element_Erpaccount'
                    ),
                    'label' => __('Assign Account'),
                    'required' => true
                ),
            )
        ));

        $this->getMassactionBlock()->addItem('assigncustomer', array(
            'label' => __('Assign Customer'),
            'url' => $this->getUrl('*/*/massAssignCustomer'),
            'additional' => array(
                'sales_rep_account' => array(
                    'name' => 'assign_customer',
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Customer',
                    'renderer' => array(
                        'type' => 'customer_selector',
                        'class' => 'Epicor_Comm_Block_Adminhtml_Form_Element_Customer'
                    ),
                    'label' => __('Customer'),
                    'required' => true
                )
            )
        ));

        $this->getMassactionBlock()->addItem('removeerpaccount', array(
            'label' => __('Remove ERP Account'),
            'url' => $this->getUrl('*/*/massRemoveErpAccount'),
            'additional' => array(
                'ecc_erp_account_type' => array(
                    'name' => 'remove_erp_account',
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpaccount',
                    'renderer' => array(
                        'type' => 'account_selector',
                        'class' => 'Epicor_Comm_Block_Adminhtml_Form_Element_Erpaccount'
                    ),
                    'label' => __('Assign Account'),
                    'required' => true
                )
            )
        ));

        $this->getMassactionBlock()->addItem('removecustomer', array(
            'label' => __('Remove Customer'),
            'url' => $this->getUrl('*/*/massRemoveCustomer'),
            'additional' => array(
                'sales_rep_account' => array(
                    'name' => 'remove_customer',
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\Customer',
                    'renderer' => array(
                        'type' => 'customer_selector',
                        'class' => 'Epicor_Comm_Block_Adminhtml_Form_Element_Customer'
                    ),
                    'label' => __('Customer'),
                    'required' => true
                )
            )
        ));


        $status_data = array('1' => __('Active'), '0' => __('Disabled'));

        $this->getMassactionBlock()->addItem('changestatus', array(
            'label' => __('Change Status'),
            'url' => $this->getUrl('*/*/massAssignStatus'),
            'additional' => array(
                'list_status' => array(
                    'name' => 'assign_status',
                    'type' => 'select',
                    'values' => $status_data,
                    'label' => __('Change Status'),
                )
            )
        ));



        return $this;
    }

    public function _statusFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        switch ($value) {
            case self::LIST_STATUS_ACTIVE:
                $collection->filterActive();
                break;

            case self::LIST_STATUS_DISABLED:
                $collection->addFieldToFilter('active', 0);
                break;

            case self::LIST_STATUS_ENDED:
                $collection->addFieldToFilter('active', 1);
                //M1 > M2 Translation Begin (Rule 25)
                //$collection->addFieldToFilter('end_date', array('lteq' => now()));
                $collection->addFieldToFilter('end_date', array('lteq' => date('Y-m-d H:i:s')));
                //M1 > M2 Translation End
                break;

            case self::LIST_STATUS_PENDING:
                $collection->addFieldToFilter('active', 1);
                //M1 > M2 Translation Begin (Rule 25)
                //$collection->addFieldToFilter('start_date', array('gteq' => now()));
                $collection->addFieldToFilter('start_date', array('gteq' => date('Y-m-d H:i:s')));
                //M1 > M2 Translation End
                break;
        }

        return $this;
    }

}
