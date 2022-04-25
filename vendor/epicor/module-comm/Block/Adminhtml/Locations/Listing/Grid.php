<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Listing;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        array $data = []
    )
    {
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('locations_grid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceLocationCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $columns = array(
            'erp_code' => array(
                'header' => __('ERP Code'),
                'align' => 'left',
                'index' => 'code',
                'type' => 'text'
            ),
            'name' => array(
                'header' => __('Name'),
                'align' => 'left',
                'index' => 'name',
                'type' => 'text'
            ),
            'company' => array(
                'header' => __('Company'),
                'align' => 'left',
                'index' => 'company',
                'type' => 'text'
            ),
            'address1' => array(
                'header' => __('Address 1'),
                'align' => 'left',
                'index' => 'address1',
                'type' => 'text'
            ),
            'address2' => array(
                'header' => __('Address 2'),
                'align' => 'left',
                'index' => 'address2',
                'type' => 'text'
            ),
            'address3' => array(
                'header' => __('Address 3'),
                'align' => 'left',
                'index' => 'address3',
                'type' => 'text'
            ),
            'city' => array(
                'header' => __('City'),
                'align' => 'left',
                'index' => 'city',
                'type' => 'text'
            ),
            'county' => array(
                'header' => __('State'),
                'align' => 'left',
                'index' => 'county',
                'type' => 'state'
            ),
            'country' => array(
                'header' => __('Country'),
                'align' => 'left',
                'index' => 'country',
                'type' => 'country'
            ),
            'postcode' => array(
                'header' => __('Postcode'),
                'align' => 'left',
                'index' => 'postcode',
                'type' => 'text'
            ),
            'location_visible' => array(
                'header' => __('Location Visible'),
                'align' => 'left',
                'index' => 'location_visible',
                'type' => 'options',
                'options' => array(
                    0 => __('No'),
                    1 => __('Yes')
                )
            ),
            'include_inventory' => array(
                'header' => __('Include Inventory'),
                'align' => 'left',
                'index' => 'include_inventory',
                'type' => 'options',
                'options' => array(
                    0 => __('No'),
                    1 => __('Yes')
                )
            ),
            'show_inventory' => array(
                'header' => __('Show Inventory'),
                'align' => 'left',
                'index' => 'show_inventory',
                'type' => 'options',
                'options' => array(
                    0 => __('No'),
                    1 => __('Yes')
                )
            ),
            'sort_order' => array(
                'header' => __('Sort Order'),
                'align' => 'left',
                'index' => 'sort_order',
                'type' => 'text'
            ),
            'telephone_number' => array(
                'header' => __('Telephone Number'),
                'align' => 'left',
                'index' => 'telephone_number',
                'type' => 'text'
            ),
            'fax_number' => array(
                'header' => __('Fax Number'),
                'align' => 'left',
                'index' => 'fax_number',
                'type' => 'text'
            ),
            'email_address' => array(
                'header' => __('Email Address'),
                'align' => 'left',
                'index' => 'email_address',
                'type' => 'text'
            )
        );

        $showColumns = explode(',', $this->scopeConfig->getValue('epicor_comm_locations/admin/grid_columns', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        foreach ($columns as $columnId => $info) {
            if (in_array($columnId, $showColumns)) {
                $this->addColumn($columnId, $info);
            }
        }

        $this->addColumn('action', array(
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
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => __('Are you sure you want to delete this Location? This cannot be undone')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'id',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('locationid');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Delete selected locations?')
        ));

        return $this;
    }

}
