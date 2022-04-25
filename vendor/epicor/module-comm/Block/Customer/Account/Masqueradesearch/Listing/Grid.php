<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Account\Masqueradesearch\Listing;


/**
 * Customer Orders list Grid config
 */
class Grid extends \Epicor\Common\Block\Generic\Listing\Grid
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
       
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $data
        );

        $this->setId('customer_account_masqueradesearch_list');
        $this->setIdColumn('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);
        $this->setMessageBase('epicor_comm');
        $this->setCustomColumns($this->_getColumns());
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setRowClickCallback('populateMasqueradeSelect');
        $this->setCacheDisabled(true);
        $erpAccountId = $this->customerSession->getCustomer()->getEccErpaccountId();
        $erpaccount = $this->commHelper->getErpAccountInfo($erpAccountId);
//        /* @var $order Epicor_Common_Model_Xmlvarien */
//        
        $children = $erpaccount->getChildAccounts();
        $erpaccount = array();
        $customer = $this->customerSession->getCustomer();
        $account = $this->salesRepAccountFactory->create()->load($customer->getEccSalesRepAccountId());
        foreach ($account->getStoreMasqueradeAccounts() as $account) {
            if ($this->isColumnEnabled('invoice_address')) {
                $addressTxt = $this->_getAddressText($account, 'default_invoice_address_code');
                $account->setInvoiceAddress($addressTxt);
            }
            if ($this->isColumnEnabled('default_shipping_address')) {
                $addressTxt = $this->_getAddressText($account, 'default_delivery_address_code');
                $account->setDefaultShippingAddress($addressTxt);
            }
            $erpaccount[$account->getName() . $account->getId()] = $account;    // save account using name as a key
        }
        $this->setCustomData($erpaccount);
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml(false);
        return $html;
    }

    protected function _getColumns()
    {
        $columns = array(
            'entity_id' => array(
                'header' => __('id'),
                'align' => 'left',
                'index' => 'entity_id',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
            'name' => array(
                'header' => __('Name'),
                'align' => 'left',
                'index' => 'name',
                'type' => 'text',
                'condition' => 'LIKE'
            ),
        );

        if ($this->isColumnEnabled('account_number')) {
            $columns['account_number'] = array(
                'header' => __('Account Number'),
                'align' => 'left',
                'index' => 'account_number',
                'condition' => 'LIKE'
            );
        }

        if ($this->isColumnEnabled('short_code')) {
            $columns['short_code'] = array(
                'header' => __('Short Code'),
                'align' => 'left',
                'index' => 'short_code',
                'type' => 'text',
                'condition' => 'LIKE'
            );
        }

        if ($this->isColumnEnabled('invoice_address')) {
            $columns['invoice_address'] = array(
                'header' => __('Invoice Address'),
                'align' => 'left',
                'index' => 'invoice_address',
                'type' => 'text',
                'condition' => 'LIKE'
            );
        }

        if ($this->isColumnEnabled('default_shipping_address')) {
            $columns['default_shipping_address'] = array(
                'header' => __('Default shipping Address'),
                'align' => 'left',
                'index' => 'default_shipping_address',
                'type' => 'text',
                'condition' => 'LIKE'
            );
        }
        return $columns;
    }

    private function _getAddressText($account, $addressCodeField)
    {
        $text = '';

        $addressCollection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create();
        /* @var $addressCollection Epicor_Comm_Model_Resource_Customer_Erpaccount_Address_Collection */
        $addressCollection->addFieldToFilter('erp_code', $account->getData($addressCodeField));
        $addressCollection->addFieldToFilter('erp_customer_group_code', $account->getErpCode());
        $address = $addressCollection->getFirstItem();
        /* @var $address Epicor_Comm_Model_Customer_Erpaccount_Address */

        if ($address) {
            $addressFields = array('name', 'address1', 'address2', 'address3', 'city', 'county', 'country', 'postcode');
            $glue = '';
            foreach ($addressFields as $field) {
                $fieldData = trim($address->getData($field));
                if ($fieldData && !empty($fieldData)) {
                    $text .= $glue . $fieldData;
                    $glue = ', ';
                }
            }
        }

        return $text;
    }

    public function isColumnEnabled($column)
    {
        return $this->scopeConfig->getValue('epicor_salesrep/masquerade_search/' . $column, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRowUrl($row)
    {
        return $row->getEntityId();
    }

}
