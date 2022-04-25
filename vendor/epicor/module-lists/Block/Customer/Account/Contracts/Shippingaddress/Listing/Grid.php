<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\Shippingaddress\Listing;

/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    private $_allowEdit;
    private $_allowDelete;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
    \Magento\Framework\Registry $registry, \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );

        $this->setId('customer_contracts_shippingaddress_list');
        $this->setIdColumn('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('epicor_lists');
        $this->setCustomColumns($this->_getColumns());
        $this->setCacheDisabled(true);
        $this->setUseAjax(true);
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $details = $this->registry->registry('epicor_lists_contracts_details');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        if ($details) {
            $shippingAddresses = $details->getContract()->getVarienDataArrayFromPath('delivery_addresses/delivery_address');
            $this->setCustomData($shippingAddresses);
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
        }
    }

    protected function _getColumns() {
        $columns = array(
            'name' => array(
                'header' => __('Name'),
                'align' => 'left',
                'index' => 'name',
                'width' => '100px',
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
            ),
        );

        return $columns;
    }

    public function getRowUrl($row) {
        return false;
    }

    public function getGridUrl() {
        return $this->getUrl('*/grid/shippingsearch', array('_current' => true));
    }

}
