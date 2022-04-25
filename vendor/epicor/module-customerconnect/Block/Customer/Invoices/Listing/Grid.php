<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Listing;

use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;
use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;
use Magento\Framework\DataObjectFactory as DataObject;

/**
 * Customer Invoices list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_invoices_export';

    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customerconnect::customerconnect_account_invoices_details';

    const FRONTEND_RESOURCE_REORDER = "Epicor_Customerconnect::customerconnect_account_invoices_reorder";

    const FRONTEND_RESOURCE_PRINT = "Epicor_Customerconnect::customerconnect_account_invoices_print";

    const FRONTEND_RESOURCE_EMAIL = "Epicor_Customerconnect::customerconnect_account_invoices_email";

    const ATTRIBUTE_TYPE = '_attributes_type';

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\ReorderFactory
     */
    protected $customerconnectCustomerInvoicesListingRendererReorderFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    private $hidePrice;
    private $dataObjectFactory;

    public function __construct(
        HidePrice $hidePrice,
        DataObject $dataObjectFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    ) {
        $this->hidePrice = $hidePrice;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
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
        $this->setId('customerconnect_invoices_grid');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cuis');
        $this->setIdColumn('invoice_number');
        $this->setEntityType('Invoice');
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');

        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportInvoicesCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportInvoicesXml'));
    }

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erpAccountNumber = $helper->getErpAccountNumber();
            $invoice = $this->urlEncoder->encode($this->encryptor->encrypt($erpAccountNumber . ']:[' . $row->getId()));
            $invoiceAttributes = $row->getData('_attributes');
            $params = [
                'invoice'        => $invoice,
                'attribute_type' => $invoiceAttributes ? $invoiceAttributes->getType() : '',
            ];
            $url = $this->getUrl('*/*/details', $params);
        }

        return $url;
    }

    private function isExportAction(): bool
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

        if ($this->listsFrontendContractHelper->contractsDisabled()) {
            unset($columns['contracts_contract_code']);
        }
        if(isset($columns['central_collection'])){
            $columns['central_collection']['filter'] = false;
            $columns['central_collection']['sortable'] = false;
            $columns['central_collection']['renderer'] = 'Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\CentrallyCollected';
        }

        if (isset($columns[self::ATTRIBUTE_TYPE])) {
            $columns[self::ATTRIBUTE_TYPE]['filter_by'] = 'ecc';
            $columns[self::ATTRIBUTE_TYPE]['renderer'] = 'Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\InvoiceType';
        }

        if (!$this->isExportAction() && ($this->isReorderAllowed() || $this->isPrintEmailAllowed())) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\Reorder',
                );
            }
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2,3])){
            $columnsToHide =  ['outstanding_value', 'original_value'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2])){
            $columnsToHide =  ['reorder'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        $this->setCustomColumns($columnObject->getData());
    }

    protected function _prepareMassaction()
    {
        return $this->initMassaction();
    }
}
