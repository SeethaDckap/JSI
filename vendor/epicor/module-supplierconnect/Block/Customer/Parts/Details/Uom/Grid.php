<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Details\Uom;


/**
 * Parts UOM grid config
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        $this->setId('supplierconnect_parts_uom');
        $this->setDefaultSort('unit_of_measure');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $this->setMessageBase('supplierconnect');
        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $part = $this->registry->registry('supplier_connect_part_details');
        $breaks = $part->getVarienDataArrayFromPath('part/suppliers_unit_of_measures/supplier_unit_of_measure');
        $this->setCustomData((array) $breaks);
    }

    protected function _getColumns()
    {

        $columns = array(
            'unit_of_measure' => array(
                'header' => __('Unit Of Measure'),
                'align' => 'left',
                'index' => 'unit_of_measure',
                'type' => 'text',
                'filter' => false
            ),
            'conversion_factor' => array(
                'header' => __('Conversion Factor'),
                'align' => 'left',
                'index' => 'conversion_factor',
                'type' => 'text',
                'filter' => false
            ),
            'operator' => array(
                'header' => __('Operator'),
                'align' => 'left',
                'index' => 'operator',
                'type' => 'text',
                'filter' => false
            ),
            'value' => array(
                'header' => __('Value'),
                'align' => 'left',
                'index' => 'value',
                'type' => 'number',
                'filter' => false
            ),
            'result' => array(
                'header' => __('Result'),
                'align' => 'left',
                'index' => 'result',
                'type' => 'text',
                'filter' => false
            ),
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
