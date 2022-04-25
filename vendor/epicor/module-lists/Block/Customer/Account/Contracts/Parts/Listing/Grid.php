<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing;

/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\StatusFactory
     */
    protected $listsCustomerAccountContractsPartsListingRendererStatusFactory;

    /**
     * @var \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\UomFactory
     */
    protected $listsCustomerAccountContractsPartsListingRendererUomFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, \Magento\Framework\Registry $registry, \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\StatusFactory $listsCustomerAccountContractsPartsListingRendererStatusFactory, \Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\UomFactory $listsCustomerAccountContractsPartsListingRendererUomFactory, array $data = []
    ) {
        $this->listsCustomerAccountContractsPartsListingRendererStatusFactory = $listsCustomerAccountContractsPartsListingRendererStatusFactory;
        $this->listsCustomerAccountContractsPartsListingRendererUomFactory = $listsCustomerAccountContractsPartsListingRendererUomFactory;
        $this->registry = $registry;
        parent::__construct(
                $context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $data
        );

        $this->setId('customer_contracts_parts_list');
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
            $parts = $details->getContract()->getVarienDataArrayFromPath('parts/part');
            $this->setCustomData($parts);
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
        }
    }

    protected function _getColumns() {
        $columns = array(
            'contract_line_number' => array(
                'header' => __('Contract Line Number'),
                'align' => 'left',
                'index' => 'contract_line_number',
                'width' => '100px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'contract_part_number' => array(
                'header' => __('Contract Part Number'),
                'align' => 'left',
                'index' => 'contract_part_number',
                'width' => '100px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'effective_start_date' => array(
                'header' => __('Start Date'),
                'align' => 'left',
                'index' => 'effective_start_date',
                'width' => '50px',
                'type' => 'date',
                'condition' => 'LIKE'
            ),
            'effective_end_date' => array(
                'header' => __('End Date'),
                'align' => 'left',
                'index' => 'effective_end_date',
                'width' => '50px',
                'type' => 'date',
                'condition' => 'LIKE'
            ),
            'line_status' => array(
                'header' => __('Line Status'),
                'align' => 'left',
                'index' => 'line_status',
                'width' => '20px',
                'type' => 'text',
                'condition' => 'LIKE',
                'renderer' => 'Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\Status',
            ),
            'product_code' => array(
                'header' => __('Sku'),
                'align' => 'left',
                'index' => 'product_code',
                'width' => '20px',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'unit_of_measures' => array(
                'header' => __('UOM'),
                'align' => 'center',
                'index' => 'unit_of_measures',
                'width' => '5px',
                'type' => 'text',
                'renderer' => 'Epicor\Lists\Block\Customer\Account\Contracts\Parts\Listing\Renderer\Uom',
            ),
        );

        return $columns;
    }

    public function _toHtml() {
        $html = parent::_toHtml();


        $html .= '<script>
                $$("tr [id ^= \'part_uom\']").each(function(a){
  
                        a.up(\'td\').observe(\'click\', function() {
                                id = a.readAttribute(\'id\').split(\'part_uom_col_\');
                                productCode = id[1];
                                if(a.innerHTML == \'+\'){          
                                        a.innerHTML = \'-\'; 
                                        $("parts_row_uom_" + productCode).show();
                                }else{
                                        $("parts_row_uom_" + productCode).hide();
                                        a.innerHTML = \'+\'; 
                                }
                        });
                })</script>';
        return $html;
    }

    public function getRowUrl($row) {
        return false;
    }

    public function getGridUrl() {
        return $this->getUrl('*/grid/partssearch', array('_current' => true));
    }

}
