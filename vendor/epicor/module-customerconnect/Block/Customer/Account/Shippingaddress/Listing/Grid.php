<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Listing;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{


    const FRONTEND_RESOURCE_UPDATE = 'Epicor_Customerconnect::customerconnect_account_information_shipping_details_update';
    const FRONTEND_RESOURCE_DELETE = 'Epicor_Customerconnect::customerconnect_account_information_shipping_details_delete';
    private $_allowEdit;
    private $_allowDelete;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $helper = $this->commonAccessHelper;

        $this->_allowEdit = $helper->customerHasAccess('Epicor_Customerconnect', 'Account', 'saveShippingAddress', '', 'Access');
        $this->_allowDelete = $helper->customerHasAccess('Epicor_Customerconnect', 'Account', 'deleteShippingAddress', '', 'Access');
        if (!$this->customerconnectHelper->checkMsgAvailable('CUAU')) {
            $this->_allowEdit = false;
            $this->_allowDelete = false;
        }

        if ($this->_allowEdit && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_UPDATE)) {
            $this->setRowClickCallback('editShippingAddress');
        }

        $this->setId('customer_account_shippingaddress_list');
        $this->setIdColumn('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('customerconnect');
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        //       $this->setRowUrlValue('*/*/editShippingAddress');

        $details = $this->registry->registry('customer_connect_account_details');
        $helper = $this->customerconnectHelper;
        if ($details) {
            $shippingAddresses = $details->getVarienDataArrayFromPath('delivery_addresss/delivery_address') ?: $details->getVarienDataArrayFromPath('delivery_addresses/delivery_address');
            $this->setCustomData($shippingAddresses);

            // this foreach is to replace the name field with shipping_name
            // this avoids the problem of searching on a grid when two grids are on one page
            // but having the filter applied to both grids (shipping and contacts share the same page and have a duplicated field of name)
            $customDataArray = $this->getCustomData();
            foreach ($customDataArray as $key => $customData) {
                $customData->setShippingName($customData->getName());
            }
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
            $this->setFilterVisibility(false);
            $this->setPagerVisibility(false);
        }
    }

    protected function _getColumns()
    {
        $columns = array(
            'shipping_name' => array(
                'header' => __('Name'),
                'align' => 'left',
                'index' => 'shipping_name',
                'width' => '100px',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Renderer\Shippingaddress',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'address1' => array(
                'header' => __('Address1'),
                'align' => 'left',
                'index' => 'address1',
                'width' => '150px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'address2' => array(
                'header' => __('Address2'),
                'align' => 'left',
                'index' => 'address2',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'address3' => array(
                'header' => __('Address3'),
                'align' => 'left',
                'index' => 'address3',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'city' => array(
                'header' => __('City'),
                'align' => 'left',
                'index' => 'city',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'postcode' => array(
                'header' => __('Postcode'),
                'align' => 'left',
                'index' => 'postcode',
                'type' => 'postcode',
                'condition' => 'LIKE'
            ),
            'country' => array(
                'header' => __('Country'),
                'align' => 'left',
                'index' => 'country',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'county' => array(
                'header' => __('State/Province'),
                'align' => 'left',
                'index' => 'county',
                'condition' => 'LIKE',
                'type' => 'state',
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Renderer\State',
            ),
            'telephone_number' => array(
                'align' => 'left',
                'index' => 'telephone_number',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
            ),
            'fax_number' => array(
                'align' => 'left',
                'index' => 'fax_number',
                'display' => 'hidden',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
            )
        );

        if (($this->_allowEdit || $this->_allowDelete) &&
            (
                $this->_isAccessAllowed(static::FRONTEND_RESOURCE_DELETE) ||
                $this->_isAccessAllowed(static::FRONTEND_RESOURCE_UPDATE)
            )
        ) {
            $columns['action'] = array(
                'header' => __('Action'),
                'id' => 'action-select',
                'type' => 'action',
                'actions' => array(),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Renderer\Actionsdropdown',
            );

            if ($this->_allowEdit && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_UPDATE)) {
                $columns['action']['actions'][] = array(
                    'caption' => __('Edit'),
                    'url' => "javascript:;",
                );
            }

            if ($this->_allowDelete && $this->_isAccessAllowed(static::FRONTEND_RESOURCE_DELETE)) {
                $columns['action']['actions'][] = array(
                    'caption' => __('Delete'),
                    'url' => "javascript:;",
                    'confirm' => __('Are you sure you want to delete this address?  This action cannot be undone.')
                );
            }
        }

        return $columns;
    }

    public function getRowUrl($row)
    {
//        $row->unsShippingName();
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/grid/shippingsearch'); // this determines which url and tab are displayed after search is complete
    }

}
