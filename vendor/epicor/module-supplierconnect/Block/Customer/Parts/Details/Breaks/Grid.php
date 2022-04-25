<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Details\Breaks;


/**
 * Parts Price breaks grid config
 * 
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
        $this->setId('supplierconnect_parts_breaks');
        $this->setDefaultSort('quantity');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageBase('supplierconnect');
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $part = $this->registry->registry('supplier_connect_part_details');
        $breaks = $part->getVarienDataArrayFromPath('part/price_breaks/price_break');
        $this->setCustomData((array) $breaks);
    }

    protected function _getColumns() {
        $columns = array(
            'quantity' => array(
                'header' => __('Minimum Quantity'),
                'align' => 'left',
                'index' => 'quantity',
                'type' => 'number',
                'filter' => false,
                'width' => '33%'
            ),
            'modifier' => array(
                'header' => __('Modifier'),
                'align' => 'left',
                'index' => 'modifier',
                'type' => 'text',
                'filter' => false,
                'width' => '33%'
            ),
            'effective_price' => array(
                'header' => __('Effective Price'),
                'align' => 'left',
                'index' => 'effective_price',
                'type' => 'number',
                'filter' => false,
                'width' => '33%'
            ),
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
