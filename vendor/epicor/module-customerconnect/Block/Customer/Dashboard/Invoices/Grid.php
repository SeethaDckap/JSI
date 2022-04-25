<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Dashboard\Invoices;


use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;
use Magento\Framework\DataObjectFactory as DataObject;

/**
 * Customer Orders list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_PRINT = "Epicor_Customerconnect::customerconnect_account_invoices_print";
    const FRONTEND_RESOURCE_EMAIL = "Epicor_Customerconnect::customerconnect_account_invoices_email";
    const FRONTEND_RESOURCE_REORDER = "Epicor_Customerconnect::customerconnect_account_invoices_reorder";
    const FRONTEND_RESOURCE_DETAIL  = "Epicor_Customerconnect::customerconnect_account_invoices_details";

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
    )
    {
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

        $this->setId('customerconnect_5recent_invoices');
        $this->setDefaultSort('invoice_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cuis');
        $this->setIdColumn('invoice_number');
        $this->setEntityType('Invoice');
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setMaxResults(5);

        $this->initColumns();
    }

    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getId()));
            $attributeType = $row->get_attributes_type();
            $url = $this->getUrl('*/invoices/details', array(
                'invoice' => $requested,
                'attribute_type' => $attributeType,
                'back' => $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()))
            ));
        }

        return $url;
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

        if ($this->isReorderAllowed() || $this->isPrintEmailAllowed()) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Invoices\Listing\Renderer\Reorder',
                );
            }
        }
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);

        if ($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1, 2, 3])) {
            $columnsToHide =  ['outstanding_value', 'original_value'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2])){
            $columnsToHide =  ['reorder'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        $this->setCustomColumns($columnObject->getData());
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/grid/invoicessearch');
    }

    protected function _prepareMassaction()
    {
        return $this->initMassaction();
    }

}
