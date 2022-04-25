<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes;


class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{
    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Dealer_Connect::dealer_claim_confirmrejects';

    private $_allowEdit;
      /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
     /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
     /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\Claimstatus
     */
    protected $_claimStatusMapping;
    
    private $quoteCount;

    protected $_editStatuses = [
        'request',
        'open'
    ];
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
       \Epicor\Common\Helper\Access $commonAccessHelper,
       \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Model\Erp\Mapping\Claimstatus $claimStatusMapping,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        $this->encryptor = $encryptor;
        $this->urlEncoder = $urlEncoder;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->request = $request;
        $this->_claimStatusMapping = $claimStatusMapping;
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );
        $helper = $this->commonAccessHelper;
        $this->_allowEdit = $helper->customerHasAccess('Epicor_Customerconnect', 'Rfqs', 'confirmreject', '', 'Access');
        if ($this->_allowEdit) {
            $helper = $this->commMessagingHelper;
            $this->_allowEdit = $helper->isMessageEnabled('customerconnect', 'crqc');
        }
        
        $this->setId('claim_quotes');
        $this->setDefaultSort('quote_number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);

        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);

        $this->setMessageBase('dealerconnect');
        $this->setMessageType('dcld');
        $this->setIdColumn('quote_number');

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        
        $claim = $this->registry->registry('dealer_connect_claim_details');
        /* @var $rfq \Epicor\Common\Model\Xmlvarien */
        if($claim){
            $quotesData = ($claim->getQuotes()) ? $claim->getQuotes()->getasarrayQuote() : array();
            $quotes = array();
            $this->quoteCount = 0;
            // add a unique id so we have a html array key for these things
            foreach ($quotesData as $row) {
                $row->setUniqueId(uniqid());
                $quotes[] = $row;
                $this->quoteCount ++;
            }

            $this->setCustomData($quotes);
        }
    }

    protected function _getColumns()
    {
        $columns = array();
        
        if ($this->isCofirmRejectAllowed() && $this->_allowEdit ) {
            $newColumns = array(
                'confirm' => array(
                    'header' => __('Confirm'),
                    'align' => 'left',
                    'index' => 'confirm',
                    'type' => 'text',
                    'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Quotes\Renderer\Confirm',
                    'filter' => false,
                    'sortable'  => false
                ),
                'reject' => array(
                    'header' => __('Reject'),
                    'align' => 'left',
                    'index' => 'reject',
                    'type' => 'text',
                    'renderer' => 'Epicor\Dealerconnect\Block\Claims\Details\Quotes\Renderer\Reject',
                    'filter' => false,
                    'sortable'  => false
                )
            );

            $columns = array_merge_recursive($newColumns, $columns);
        }
        
        $columns['quote_number'] = array(
            'header' => __('Quote Number'),
            'align' => 'left',
            'index' => 'quote_number',
            'type' => 'text',
            'filter' => false,
            'sortable'  => false
        );
        $columns['quote_date'] = array(
            'header' => __('Quote Date'),
            'align' => 'left',
            'index' => 'quote_date',
            'type' => 'date',
            'filter' => false,
            'sortable'  => false
        );
        $columns['due_date'] = array(
            'header' => __('Due Date'),
            'align' => 'left',
            'index' => 'due_date',
            'type' => 'date',
            'filter' => false,
            'sortable'  => false
        );
        $columns['quote_status'] = array(
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'quote_status',
            'type' => 'options',
            'filter' => false,
            'sortable'  => false,
            'options' => 'customerconnect/erp_mapping_erpquotestatus',
            'renderer' => 'Epicor\Customerconnect\Block\Listing\Renderer\Erpquotestatus'
        );

        return $columns;
    }

    public function getRowUrl($row)
    {
         $html = '';
        $erpAccountNum = $this->commMessagingHelper->getErpAccountNumber();
        $quoteDetails = array(
            'erp_account' => $erpAccountNum,
            'quote_number' => $row->getQuoteNumber(),
            'quote_sequence' => $row->getQuoteSequence(),
        );
            $requested = $this->urlEncoder->encode($this->encryptor->encrypt(serialize($quoteDetails)));
            $requestParam = [
                'quote' => $requested,
                'can_editclaim' => $this->canEditClaim()
            ];
            $url = $this->getUrl('*/*/quotedetails', $requestParam);
           return "javascript:window.dealerClaim.editQuote('".$url."');";
    }
    
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($this->isCofirmRejectAllowed() && $this->_allowEdit && !$this->request->isAjax() && $this->quoteCount> 0) {
            $html .= '<div class="button-rfq-action">
                <button id="claim_rfq_confirmreject_save" class="scalable" type="button">' . __('Confirm / Reject RFQs') . '</button>
            </div>';
        }

        return $html;
    }

    /**
     * Show Confirm/Reject only if Claim status is Open or Request
     * @return bool
     */
    protected function isCofirmRejectAllowed()
    {
        $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CONFIRMREJECT);
        if ($allowed == true) {
            $allowed = ($this->canEditClaim() == 1) ? true : false;

        }
        return $allowed;
    }

    /**
     * @return bool
     */
    protected function canEditClaim()
    {
        $allowed = 1;
        $claim = $this->registry->registry('dealer_connect_claim_details');
        $status = $claim->getStatus();
        if ($status != '' && $status == 'CLOSED') {
            $allowed = 0;
        }
        $claimStatus = $claim->getClaimStatus();
        if (!is_null($claimStatus)) {
            $editErpStatusCode = $this->_claimStatusMapping
                ->getClaimStatus($this->_editStatuses)
                ->getData();
            $_editStatusCode = array_column($editErpStatusCode, 'erp_code');
            if (!empty($_editStatusCode)
                && !in_array($claimStatus, $_editStatusCode)
            ) {
                $allowed = 0;
            }
        }
        return $allowed;
    }
    
}
