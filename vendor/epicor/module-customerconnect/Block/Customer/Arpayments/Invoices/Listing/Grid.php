<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Listing;

/**
 *
 *
 * 
 *
 * @category   Epicor
 * @package    Epicor_CustomerConnect
 * @author     Epicor Websales Team
 */
class Grid extends \Epicor\Customerconnect\Block\Customer\Arpayments\Generic\Listing\Search
{

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
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    private $dataObjectFactory;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;       
    
    
    protected $arpaymentsHelper;    

    public function __construct(\Magento\Backend\Block\Template\Context $context, 
                                \Magento\Backend\Helper\Data $backendHelper, 
                                \Epicor\Customerconnect\Model\Message\ArcollectionFactory $commonMessageCollectionFactory, 
                                \Epicor\Common\Helper\Data $commonHelper, 
                                \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper, 
                                \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader, 
                                \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader, 
                                \Epicor\Common\Helper\Access $commonAccessHelper, 
                                \Epicor\Comm\Helper\Messaging $commMessagingHelper, 
                                \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper, 
                                \Epicor\Comm\Helper\Data $commHelper,
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
                                \Magento\Framework\Url\EncoderInterface $urlEncoder, 
                                \Magento\Framework\Encryption\EncryptorInterface $encryptor, 
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\DataObjectFactory $dataObjectFactory, array $data = [])
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->registry = $registry;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commHelper = $commHelper;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->setRowClickCallback(null);
        parent::__construct($context, $backendHelper, $commonMessageCollectionFactory, $commonHelper, $frameworkHelperDataHelper, $configOptionsModelReader, $columnRendererReader, $data);

        $this->setFooterPagerVisibility(false);
        $helper = $this->commonAccessHelper;

        $this->setId('customer_arpayments_invoices_list');
        $this->setDefaultSort('invoice_number');
        $this->setDefaultDir('asc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('caps');
        $this->setRequestMessageBody(true);
        $this->setIdColumn('invoice_number');
        $this->setUseAjax(true);
        $this->initColumns();
        $this->setCountTotals(true);
        $checkAgedFilter = $this->returnDefaultFilter();
        $this->setDefaultFilter(array(
            'aged_period_number' => $checkAgedFilter
        ));
        $jsObjectName = $this->getJsObjectName();
        //When the user click's on reset button all the grid values will get removed 
        //but we need to post aged period number in all requests
        //customerconnect_arpayments_aged_gridJsObject.resetFilter();
        $this->setAdditionalJavaScript("
            $jsObjectName.resetFilter = function(callback) {
                var filters = $$('#' + this.containerId + ' [data-role=\"filter-form\"] input', '#' + this.containerId + ' [data-role=\"filter-form\"] select');
                var elements = [];
                for (var i in filters) {
                    if(filters[i].name==\"aged_period_number\" && filters[i].value && filters[i].value.length) {
                        if (filters[i].value && filters[i].value.length) elements.push(filters[i]);
                    }
                }
                if (!this.doFilterCallback || (this.doFilterCallback && this.doFilterCallback())) {
                    this.reload(this.addVarToUrl(this.filterVar, Base64.encode(Form.serializeElements(elements))), callback);
                }              
               };          
            ");
        //When the user lands on the AR Payments Page for the first time, then we are getting the values from registry
        //because we are using two grids in this page and we need to send 1 message to ERP
        $details = $this->registry->registry('customer_connect_arpayments_details');
        if ($details){
            $helper = $this->commHelper;
            $invoices = $details[0]->getVarienDataArrayFromPath('invoices/invoice');
            $this->setCustomData($invoices);
        }
    }

    public function getRowClickCallback()
    {
        return '';
    }

    public function getRowUrl($row)
    {
        return false;
    }
    
    /**
     * Aged Filter Handler
     * based on aged filter we need to filter the request
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */    

    public function returnDefaultFilter()
    {
        $columnId = $this->getParam($this->getVarNameSort() , $this->_defaultSort);
        $columnIdParam = $this->getRequest()
            ->getParam('sort');
        $columnIdAged = $this->getRequest()
            ->getParam('agedfilter');
        $valuesAged = explode('_', $columnIdAged);
        $values = explode('_', $columnId);
        $valuesParam = explode('_', $columnIdParam);
        $checkEmpty = '';
        //capture the aged grid value and do a  filter
        //If the filter is a aged grid filter then do a internal filter by using linq
        if (($valuesAged[0] == "aged") || ($values[0] == "aged") || ($valuesParam[0] == "aged"))
        {
            $resetSort = $this->getParam('dir');
            if ($columnIdAged)
            {
                $assignVals = ($columnIdAged) ? $valuesAged : $values;
            }
            else
            {
                $assignVals = ($columnIdParam) ? $valuesParam : $values;
            }

            $lastNumber = end($assignVals);
            $assignNumber = (int)$lastNumber;
            $checkEmpty = !empty($assignNumber) ? ($resetSort == "asc") ? $assignNumber : "" : '';
            if (!empty($assignNumber))
            {
                $this->setInternalFilter(true);
            }
        }
        return $checkEmpty;

    }


    protected function initColumns()
    {
        parent::initColumns();
        $columns = $this->getCustomColumns();
        
        
        $columns['term_balance'] = array(
                    'header' => 'Term Amount',
                    'type' => 'text',
                    'index' => 'term_balance',
                    'filter' => false,
                    'class' =>'validate-number',
                    'sortable' => false,
                    'is_system' => true,
                    'renderer' => 'Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer\TermAmount',
        );         

        $columns['arpayment_amount'] = array(
            'header' => __('Total Payment') ,
            'type' => 'text',
            'index' => 'arpayment_amount',
            'filter' => false,
            'class' => 'validate-number',
            'sortable' => false,
            'renderer' => 'Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer\PaymentAmount',
            'is_system' => true,
        );
        
        $allowDispute =$this->arpaymentsHelper->checkDisputeAllowedOrNot();
        if($allowDispute) {
            $columns['dispute_invoice'] = array(
                'header' => __('Dispute') ,
                'type' => 'text',
                'index' => 'dispute_invoice',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer\Dispute',
                'is_system' => true,
            );
        }

        $columns['aged_period_number'] = array(
            'header' => 'aged_period_number',
            'index' => 'aged_period_number',
            'filter_index' => 'aged_period_number',
            'condition' => 'EQ',
            'filter_by' => 'linq',
            'type' => 'text',
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        );
        if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_payment_details")) {
            $columns['details'] = array(
                'header' => __('Details'),
                'type' => 'text',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer\Details',
                'is_system' => true
            );
        }

        $this->setCustomColumns($columns);

    }

    /**
     * Display the Totals sections in the bottom
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function getTotals()
    {
        $totals = $this
            ->dataObjectFactory
            ->create();
        $fields = array(
            'outstanding_value' => 0, //actual column index, see _prepareColumns()
            'payment_value' => 0,
            'original_value' => 0,
            'arpayment_amount' => 0
        );
       $count = $this->getCollection()->Count();
        if ($count > 0) {
            foreach ($this->getCollection() as $item) {
                foreach ($fields as $field => $value) {
                    $fields[$field] += $item->getData($field);
                }
            }
        }
        //First column in the grid
        $fields['select_arpayments'] = 'Totals';
        $totals->setData($fields);
        return $totals;
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_payment_payment")) {
            $this->addColumn('select_arpayments', ['header' => __(''),
                'type' => 'checkbox',
                'name' => 'select_arpayments',
                'field_name' => 'select_arpayments',
                'values' => $this->_getSelected(),
                'align' => 'center',
                'editable' => true,
                'edit_only' => true,
                'mask'      => !$this->arpaymentsHelper->getIsInVoiceEditSupported(),
                'index' => 'invoice_number',
                'header_css_class' => 'col-select',
                'column_css_class' => 'col-select',

            ]);
        }
        return parent::_prepareColumns();
    }

    
    /**
     * Getting the getSelected values for Grid serializer
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */    

    protected function _getSelected()
    {
        return $this->getSelected();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $columnId = $this->getRequest()
            ->getParam('arfiltergrid');
        if ($columnId)
        {
            //When a user doing a search recalculate the amount using javascript
            $html .= '<script>arPaymentsJs.calculateArAmountAllocate();</script>';
        }
        return $html;
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $helper = $this->commHelper;

        $items = $this->getCollection()->getItems();
        if (is_array($items))
        {
            foreach ($items as $item)
            {
                $helper->sanitizeData($item);
            }
        }
    }
    
    protected function _prepareLayout()
    {
        if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_payment_payment")) {
            $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Clear allocations'),
                    'onclick' => 'arPaymentsJs.clearAllocatedInvoiceAmount()',
                    'class' => 'action-secondary'
                ))
            );
        }
        return parent::_prepareLayout();
    }    
    
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAddButtonHtml();
        $html .= parent::getMainButtonsHtml();
        return $html;
    }    

    public function getGridUrl()
    {
        $columnId = $this->getRequest()
            ->getParam('sort');
        //return $this->getUrl('*/*/grid');
        return $this->getUrl('*/*/grid', array(
            '_current' => true,
            '_query' => array(
                'arfiltergrid' => 'true'
            )
        ));
    }

}