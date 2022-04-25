<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Crqs\Listing;


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

    private $_allowEdit;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Common\Model\GridConfigOptionsModelReader $configOptionsModelReader,
        \Epicor\Common\Block\Generic\Listing\ColumnRendererReader $columnRendererReader,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerconnectHelper = $customerconnectHelper;
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

        $this->setId('customerconnect_rfqs');
        $this->setDefaultSort('quote_number');
        $this->setDefaultDir('desc');
        $this->setMessageBase('customerconnect');
        $this->setMessageType('crqs');
        $this->setIdColumn('quote_number');
        $this->initColumns();
        $this->setCacheDisabled(true);
        $this->setExportTypeCsv(array('text' => 'CSV', 'url' => '*/*/exportCsv'));
        $this->setExportTypeXml(array('text' => 'XML', 'url' => '*/*/exportXml'));

        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $salesRepAccount = $customer->getSalesRepAccount();
        /* @var $salesRepAccount Epicor_SalesRep_Model_Account */

        $erpAccounts = $salesRepAccount->getStoreMasqueradeAccounts();

        $erpAccountsCodes = array();
        foreach ($erpAccounts as $erpAccount) {
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            $erpAccountsCodes[] = $erpAccount->getAccountNumber();
        }

        $_POST['selectederpaccts'] = $this->commHelper->getUrlEncoder()->encode(join(',', $erpAccountsCodes));
        $this->request->setParams(['selectederpaccts'=>$this->commHelper->getUrlEncoder()->encode(join(',', $erpAccountsCodes))]);
    }

    public function getRowUrl($row)
    {

        $url = null;
        $accessHelper = $this->commonAccessHelper;
        /* @var $helper Epicor_Common_Helper_Access */

        $msgHelper = $this->commMessagingHelper;
        /* @var $msgHelper Epicor_Comm_Helper_Messaging */
        $enabled = $msgHelper->isMessageEnabled('customerconnect', 'crqd');

        if ($enabled && $accessHelper->customerHasAccess('Epicor_SalesRep', 'Crqs', 'details', '', 'Access')) {

            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Data */

            $quoteDetails = array(
                'erp_account' => $row->getAccountNumber(),
                'quote_number' => $row->getQuoteNumber(),
                'quote_sequence' => $row->getQuoteSequence()
            );

            $requested = $this->commHelper->getUrlEncoder()->encode($helper->getEncryptor()->encrypt(serialize($quoteDetails)));
            $url = $this->getUrl('*/*/details', array('quote' => $requested));
        }

        return $url;
    }

    protected function initColumns()
    {
        parent::initColumns();

        $columns = $this->getCustomColumns();
        
        $newColumns = array(
            'account_number' => array(
                'header' => __('Account Number'),
                'align' => 'left',
                'index' => 'account_number',
                'renderer' => 'Epicor\SalesRep\Block\Crqs\Listing\Renderer\AccountShortcode',
                'type' => 'text',
                'filter' => false
            )
        );
        $columns['dealer_grand_total_inc']['column_css_class'] = 'no-display';
        $columns['dealer_grand_total_inc']['header_css_class'] = 'no-display';
        
        $columns = array_merge_recursive($newColumns, $columns);
       
        $this->setCustomColumns($columns);
    }

}
