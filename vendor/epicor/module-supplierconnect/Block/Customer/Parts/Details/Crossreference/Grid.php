<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Details\Crossreference;


/**
 * Parts crossreference grid config
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

        $this->setId('supplierconnect_parts_crossreference');
        $this->setDefaultSort('manufacturer');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        $this->setMessageBase('supplierconnect');

        $part = $this->registry->registry('supplier_connect_part_details');
        $crossReferences = $part->getVarienDataArrayFromPath('part/cross_reference_parts/cross_reference_part');
        $this->setCustomData((array) $crossReferences);
    }

    protected function _getColumns()
    {

        $columns = array(
            'manufacturer_name' => array(
                'header' => __('Qualified Manufacturer'),
                'align' => 'left',
                'index' => 'manufacturer_name',
                'type' => 'text',
                'filter' => false
            ),
            'manufacturers_product_code' => array(
                'header' => __('Manufacturer\'s Part'),
                'align' => 'left',
                'index' => 'manufacturers_product_code',
                'type' => 'text',
                'filter' => false
            ),
            'supplier_product_code' => array(
                'header' => __('Supplier Part'),
                'align' => 'left',
                'index' => 'supplier_product_code',
                'type' => 'text',
                'filter' => false
            ),
            'supplier_lead_days' => array(
                'header' => __('Supplier\'s Lead Days'),
                'align' => 'left',
                'index' => 'supplier_lead_days',
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
