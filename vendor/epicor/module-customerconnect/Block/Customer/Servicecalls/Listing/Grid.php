<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Servicecalls\Listing;


/**
 * Customer Service Call list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_service_calls_export';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $configOptionsModelReader,
            $columnRendererReader,
            $data
        );

        $this->setFooterPagerVisibility(true);
        $this->setId('customerconnect_servicecalls');
        $this->setDefaultSort('call_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cucs');
        $this->setIdColumn('call_number');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportServicecallsCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportServicecallsXml'));
    }

    public function getRowUrl($row)
    {
        return null;
    }

}
