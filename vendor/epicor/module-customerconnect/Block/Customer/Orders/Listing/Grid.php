<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Orders\Listing;


use Epicor\Comm\Helper\Data as CommHelper;
use Epicor\Customerconnect\Helper\ExportFile as ExportFileHelper;
use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;

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


    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_orders_export';

    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customerconnect::customerconnect_account_orders_details';

    const FRONTEND_RESOURCE_REORDER = "Epicor_Customerconnect::customerconnect_account_orders_reorder";

    const FRONTEND_RESOURCE_PRINT = "Epicor_Customerconnect::customerconnect_account_orders_print";

    const FRONTEND_RESOURCE_EMAIL = "Epicor_Customerconnect::customerconnect_account_orders_email";
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
     * @var \Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\ReorderFactory
     */
    protected $customerconnectCustomerDashboardOrdersRendererReorderFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    protected $hidePrice;

    public function __construct(
        HidePrice $hidePrice,
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
        \Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\ReorderFactory $customerconnectCustomerDashboardOrdersRendererReorderFactory,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    )
    {
        $this->customerconnectCustomerDashboardOrdersRendererReorderFactory = $customerconnectCustomerDashboardOrdersRendererReorderFactory;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $context->getEventManager();
        $this->hidePrice = $hidePrice;
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
        $this->setId('customerconnect_orders');
        $this->setDefaultSort('order_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('cuos');
        $this->setIdColumn('order_number');
        $this->setEntityType('Order');
        $this->setNoFilterMassactionColumn(true);
        $this->setMassactionBlockName('Epicor\Common\Block\Widget\Grid\Massaction\Extended');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportOrdersCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportOrdersXml'));
    }


    public function getRowUrl($row)
    {
        $url = null;
        if ($this->isRowUrlAllowed()) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $order_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getId()));
            $url = $this->getUrl('*/*/details', array('order' => $order_requested));
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
        
        if ($this->listsFrontendContractHelper->contractsDisabled()) {
            unset($columns['contracts_contract_code']);
        }

        if (!$this->isExportAction() && ($this->isReorderAllowed() || $this->isPrintEmailAllowed()) ) {
            if ($this->scopeConfig->getValue('sales/reorder/allow', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $columns['reorder'] = array(
                    'header' => __('Action'),
                    'type' => 'text',
                    'filter' => false,
                    'sortable' => false,
                    'header_css_class' => 'action-link-ht',
                    'column_css_class' => 'action-link-ht',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Renderer\Reorder',
                );
            }
        }

        $columns['dealer_grand_total_inc']['column_css_class'] = 'no-display';
        $columns['dealer_grand_total_inc']['header_css_class'] = 'no-display';
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);
        $this->eventManager->dispatch('epicor_customerconnect_cuos_grid_columns_after', array(
            'block' => $this,
            'columns' => $columnObject
            )
        );
        if ($this->getIsExport()) {
            $dealerData = $columnObject->getData('dealer_grand_total_inc');
            if ($dealerData['header_css_class'] === "no-display") {
                $columnObject->unsetData('dealer_grand_total_inc');
            } else {
                $columnObject->unsetData('original_value');
            }
        }

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2,3])){
            $columnsToHide =  ['original_value', 'dealer_grand_total_inc'];
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
