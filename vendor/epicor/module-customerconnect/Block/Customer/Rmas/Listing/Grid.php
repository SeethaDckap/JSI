<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rmas\Listing;


use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;

/**
 * Customer RMA list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{
    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_rma_export';

    const FRONTEND_RESOURCE_PRINT = "Epicor_Customerconnect::customerconnect_account_rma_print";

    const FRONTEND_RESOURCE_EMAIL = "Epicor_Customerconnect::customerconnect_account_rma_email";

    private $commonAccessHelper;
    private $dataObjectFactory;

    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Common\Helper\Access $commonAccessHelper,
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
        $this->commonAccessHelper = $commonAccessHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->setFooterPagerVisibility(true);
        $this->setId('customerconnect_rmas');
        $this->setDefaultSort('returns_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('curs');
        $this->setIdColumn('returns_number');
        $this->setEntityType('RMA');
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportRmasCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportRmasXml'));
    }
    private function isPreqActive(): bool
    {
        return (bool) $this->_scopeConfig->getValue('customerconnect_enabled_messages/PREQ_request/active');
    }

    private function isErpE10(): bool
    {
        return  $this->_scopeConfig->getValue('Epicor_Comm/licensing/erp') === 'e10';
    }

    private function isExportAction()
    {
        return ExportFileHelper::isExportAction(
            $this->getRequest()->getActionName(),
            $this->getRequest()->getModuleName()
        );
    }

    protected function initColumns(){
        parent::initColumns();
        $columns = $this->getCustomColumns();
        $helper = $this->commonAccessHelper;

        if (!$this->isExportAction() && ($this->isPrintEmailAllowed()
                && $this->isPreqActive()
                && $this->isErpE10())
        ) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => '\Epicor\Customerconnect\Block\Customer\Rmas\Listing\Renderer\Reorder',
                );
            }
        }

        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        $this->setCustomColumns($columnObject->getData());
    }

    public function getRowUrl($row)
    {
        return null;
    }

    protected function _prepareMassaction()
    {
        return $this->initMassaction();
    }
}
