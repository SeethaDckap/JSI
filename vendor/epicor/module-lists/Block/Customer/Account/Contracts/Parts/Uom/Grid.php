<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\Parts\Uom;

/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Block\Customer\Account\Contracts\parts\Listing\Renderer\CurrenciesFactory
     */
    protected $listsCustomerAccountContractsPartsListingRendererCurrenciesFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Epicor\Lists\Helper\Data $listsHelper, \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\CurrenciesFactory $listsCustomerAccountContractsPartsListingRendererCurrenciesFactory, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, \Magento\Framework\Registry $registry, \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\StatusFactory $listsCustomerAccountContractsPartsListingRendererStatusFactory, \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\UomFactory $listsCustomerAccountContractsPartsListingRendererUomFactory, array $data = []
    ) {
        $this->listsCustomerAccountContractsPartsListingRendererCurrenciesFactory = $listsCustomerAccountContractsPartsListingRendererCurrenciesFactory;
        $this->registry = $registry;
        $this->listsHelper = $listsHelper;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );

        $this->setId('customer_contracts_parts_uom');
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
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);

        $details = $this->registry->registry('contracts_parts_row');   // as this is a sub grid, this is set in the uom renderer and is an array of varien objects
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */
        if ($details) {
            $this->setCustomData($details);
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
            $this->setFilterVisibility(false);
            $this->setPagerVisibility(false);
        }
    }

    protected function _getColumns() {
        $columns = array(
            'unit_of_measure_code' => array(
                'header' => __('Uom Code'),
                'align' => 'left',
                'index' => 'unit_of_measure_code',
                'width' => '50px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'minimum_order_qty' => array(
                'header' => __('Min Order Qty'),
                'align' => 'left',
                'index' => 'minimum_order_qty',
                'width' => '50px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'maximum_order_qty' => array(
                'header' => __('Max Order Qty'),
                'align' => 'left',
                'index' => 'maximum_order_qty',
                'width' => '50px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'contract_qty' => array(
                'header' => __('Contract Qty'),
                'align' => 'left',
                'index' => 'contract_qty',
                'width' => '50px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'is_discountable' => array(
                'header' => __('Is Discountable'),
                'align' => 'left',
                'index' => 'is_discountable',
                'width' => '10px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'currencies' => array(
                'header' => __('Price'),
                'align' => 'left',
                'index' => 'currencies',
                'width' => '50px',
                'type' => 'text',
                'renderer' => 'Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\Currencies',
                'condition' => 'LIKE'
            ),
        );

        return $columns;
    }

    public function getRowUrl($row) {
        return false;
    }

}
