<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Listing;

use Epicor\Customerconnect\Model\EccHidePrices\HidePrice as HidePrice;

/**
 * Customer RFQ list Grid config
 * 
 * Note: columns for this grid are configured in the Magento Admin: Configuration > Customer Connect
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Search
{

    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Epicor_Customerconnect::customerconnect_account_rfqs_confirmrejects';

    const FRONTEND_RESOURCE_DETAIL = 'Epicor_Customerconnect::customerconnect_account_rfqs_details';

    const FRONTEND_RESOURCE_EXORT = 'Epicor_Customerconnect::customerconnect_account_rfqs_export';

    private $_allowEdit;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

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
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    private $hidePrice;

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
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    )
    {
        $this->hidePrice = $hidePrice;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $context->getEventManager();
        $this->request = $request;
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
        $helper = $this->commonAccessHelper;

        $this->_allowEdit = $helper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'confirmreject', '', 'Access');
        if ($this->_allowEdit) {
            $helper = $this->commMessagingHelper;
            $this->_allowEdit = $helper->isMessageEnabled('customerconnect', 'crqc');
        }

        $this->setId('customerconnect_rfqs');
        $this->setDefaultSort('quote_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('crqs');
        $this->setIdColumn('quote_number');
        $this->initColumns();
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportXml'));
    }

    public function getRowUrl($row)
    {

        $url = null;
        if ($this->isRowUrlAllowed()) {
            $accessHelper = $this->commonAccessHelper;

            $msgHelper = $this->commMessagingHelper;
            $enabled = $msgHelper->isMessageEnabled('customerconnect', 'crqd');

            if ($enabled && $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'details', '', 'Access')) {
                $helper = $this->customerconnectHelper;
                $erpAccountNum = $helper->getErpAccountNumber();

                $quoteDetails = array(
                    'erp_account' => $erpAccountNum,
                    'quote_number' => $row->getQuoteNumber(),
                    'quote_sequence' => $row->getQuoteSequence()
                );

                $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($quoteDetails)));
                $url = $this->getUrl('*/*/details', array('quote' => $requested));
            }
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

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT) && $this->_allowEdit && !$this->getIsExport()) {
            $newColumns = array(
                'confirm' => array(
                    'header' => __('Confirm'),
                    'align' => 'left',
                    'index' => 'confirm',
                    'type' => 'text',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Listing\Renderer\Confirm',
                    'filter' => false
                ),
                'reject' => array(
                    'header' => __('Reject'),
                    'align' => 'left',
                    'index' => 'reject',
                    'type' => 'text',
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Rfqs\Listing\Renderer\Reject',
                    'filter' => false
                )
            );

            $columns = array_merge_recursive($newColumns, $columns);
        }

        $columns['dealer_grand_total_inc']['column_css_class'] = 'no-display';
        $columns['dealer_grand_total_inc']['header_css_class'] = 'no-display';
        $columnObject = $this->dataObjectFactory->create(['data' => $columns]);
        $this->eventManager->dispatch('epicor_customerconnect_crqs_grid_columns_after', array(
            'block' => $this,
            'columns' => $columnObject
            )
        );
        if ($this->getIsExport()) {
            $dealerData = $columnObject->getData('dealer_grand_total_inc');
            if ($dealerData['header_css_class'] === "no-display") {
                $columnObject->unsetData('dealer_grand_total_inc');
            }else{
                $columnObject->unsetData('original_value');
            }
        }

        if($this->hidePrice->getHidePrices() && in_array($this->hidePrice->getHidePrices(), [1,2,3])){
            $columnsToHide = ['original_value', 'dealer_grand_total_inc'];
            $this->hidePrice->hidePriceColumns($columnObject, $columnsToHide);
        }

        $this->setCustomColumns($columnObject->getData());
   
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT) && $this->_allowEdit && !$this->request->isAjax()) {
            $html .= '<div class="button-rfq-action">
                <div id="running_total" style="display:none"><p>' . __('Total Confirmed') . ': <span id="running_total_price"></span></p></div>
                <button id="rfq_confirmreject_save" class="scalable" type="button">' . __('Confirm / Reject RFQs') . '</button>
            </div>';
        }

        return $html;
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $helper = $this->commHelper;

        $items = $this->getCollection()->getItems();
        if (is_array($items)) {
            foreach ($items as $item) {
                $helper->sanitizeData($item);
            }
        }
    }
    
}
