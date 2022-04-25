<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Shipments\Listing;


use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;
use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;
use Magento\Framework\DataObjectFactory as DataObjectFactory;
/**
 * Customer Shipments list Grid config
 *
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_shipments_export';

    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customerconnect::customerconnect_account_shipments_details';

    const FRONTEND_RESOURCE_REORDER = 'Epicor_Customerconnect::customerconnect_account_shipments_reorder';

    const FRONTEND_RESOURCE_PRINT = 'Epicor_Customerconnect::customerconnect_account_shipments_print';

    const FRONTEND_RESOURCE_EMAIL = 'Epicor_Customerconnect::customerconnect_account_shipments_email';

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    private $hidePrice;
    private $dataObjectFactory;

    public function __construct(
        HidePrice $hidePrice,
        DataObjectFactory $dataObjectFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    )
    {
        $this->hidePrice = $hidePrice;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->encryptor = $encryptor;
        $this->urlEncoder = $urlEncoder;
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
        $this->setId('customerconnect_shipments');
        $this->setDefaultSort('shipment_date');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cuss');
        $this->setIdColumn('packing_slip');
        $this->setEntityType('Pack');
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportShipmentsCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportShipmentsXml'));
    }

    public function getRowUrl($row)
    {

        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erpAccountNumber = $helper->getErpAccountNumber();
            $shipDetails = $this->encryptor->encrypt($erpAccountNumber . ']:[' . $row->getId() . ']:[' . $row->getOrderNumber());
            $shipment = $this->urlEncoder->encode($shipDetails);
            $url = $this->getUrl('*/*/details', array('shipment' => $shipment));
        }

        return $url;
    }

    private function isExportAction()
    {
        return ExportFileHelper::isExportAction(
            $this->getRequest()->getActionName(),
            $this->getRequest()->getModuleName()
        );
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();

        $helper = $this->commonAccessHelper;

        if (!$this->isExportAction() && ($this->isReorderAllowed() || $this->isPrintEmailAllowed())) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Shipments\Listing\Renderer\Reorder',
                );
            }
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2])) {
            $columnsToHide = ['reorder'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        $this->setCustomColumns($columnObject->getData());
    }

    protected function _prepareMassaction()
    {
        return $this->initMassaction();
    }
}
