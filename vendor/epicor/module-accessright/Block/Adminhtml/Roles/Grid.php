<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles;

use \Epicor\Comm\Model\Customer\Erpaccount as Erpaccount;
/**
 * Role admin actions
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    const LIST_STATUS_ACTIVE = 'A';
    const LIST_STATUS_DISABLED = 'D';
    const LIST_STATUS_ENDED = 'E';
    const LIST_STATUS_PENDING = 'P';

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory
     */
    protected $rolesResourceRoleModelCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory $rolesResourceRoleModelCollectionFactory,
        array $data = []
    )
    {
        $this->rolesResourceRoleModelCollectionFactory = $rolesResourceRoleModelCollectionFactory;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('accessright_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->rolesResourceRoleModelCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'id', array(
            'header' => __('ID'),
            'index' => 'id',
            'type' => 'number'
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
            'priority', array(
                'header' => __('Priority'),
                'index' => 'priority',
                'type' => 'number'
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
                'sortable' => false,
                'filter_condition_callback' => array($this, '_statusFilter'),
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
                    'confirm' => __('Are you sure you want to delete this Role? This cannot be undone')
                ),
                array(
                    'caption' => __('Duplicate'),
                    'url' => array('base' => '*/*/duplicate'),
                    'field' => 'cid'
                ),
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
        $this->getMassactionBlock()->setFormFieldName('roleid');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected Role?')
        ));

        $this->getMassactionBlock()->addItem('assignerpaccount', array(
            'label' => __('Assign ERP Account'),
            'url' => $this->getUrl('*/*/massAssignErpAccount'),
            'additional' => array(
                'ecc_erp_account_type' => array(
                    'name' => 'assign_erp_account',
                    'allowedAccountTypes' => [
                        Erpaccount::CUSTOMER_TYPE_B2B,
                        Erpaccount::CUSTOMER_TYPE_B2C,
                        Erpaccount::CUSTOMER_TYPE_Dealer,
                        Erpaccount::CUSTOMER_TYPE_Distributor,
                        Erpaccount::CUSTOMER_TYPE_SUPPLIER,
                    ],
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\AllowedErpaccounts',
                    'label' => __('Assign Account'),
                    'required' => true
                ),
            )
        ));

        /* Pass the notAllowedCustomerTypes field value
        * if you do not want to show customer of certain Customer Type
        * guest, salesrep, supplier and customer(has three types b2b, dealer , distributor)
        */

        $this->getMassactionBlock()->addItem('assigncustomer', array(
            'label' => __('Assign Customer'),
            'url' => $this->getUrl('*/*/massAssignCustomer'),
            'additional' => array(
                'sales_rep_account' => array(
                    'name' => 'assign_customer',
                    'notAllowedCustomerTypes' => [],
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\AllowedCustomer',
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
                    'allowedAccountTypes' => [
                        Erpaccount::CUSTOMER_TYPE_B2B,
                        Erpaccount::CUSTOMER_TYPE_B2C,
                        Erpaccount::CUSTOMER_TYPE_Dealer,
                        Erpaccount::CUSTOMER_TYPE_Distributor,
                        Erpaccount::CUSTOMER_TYPE_SUPPLIER,
                        ],
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\AllowedErpaccounts',
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
                    'notAllowedCustomerTypes' => [],
                    'type' => 'Epicor\Comm\Block\Adminhtml\Form\Element\AllowedCustomer',
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
