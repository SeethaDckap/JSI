<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Manage\Select;


/**
 * Branchpickup select page grid
 *
 * @category   Epicor
 * @package    Epicor_Branchpickup
 * @author     Epicor Websales Team
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $_selected = array();
    protected $_salesrepChildrenIds;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;


    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\InvoiceFactory
     */
    protected $salesRepManageSelectGridRendererInvoiceFactory;

    /**
     * @var \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\ShippingFactory
     */
    protected $salesRepManageSelectGridRendererShippingFactory;

    /**
     * @var \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\SelectFactory
     */
    protected $salesRepManageSelectGridRendererSelectFactory;

    protected $_salesrep;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $encoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Epicor\SalesRep\Helper\Account\Manage $salesRepAccountManageHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\InvoiceFactory $salesRepManageSelectGridRendererInvoiceFactory,
        \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\ShippingFactory $salesRepManageSelectGridRendererShippingFactory,
        \Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\SelectFactory $salesRepManageSelectGridRendererSelectFactory,
        \Magento\Framework\Url\EncoderInterface $encoder,
        array $data = []
    )
    {
        $this->salesRepManageSelectGridRendererInvoiceFactory = $salesRepManageSelectGridRendererInvoiceFactory;
        $this->salesRepManageSelectGridRendererShippingFactory = $salesRepManageSelectGridRendererShippingFactory;
        $this->salesRepManageSelectGridRendererSelectFactory = $salesRepManageSelectGridRendererSelectFactory;
        $this->customerSession = $customerSession;
        $this->salesRepHelper = $salesRepHelper;
        $this->salesRepAccountManageHelper = $salesRepAccountManageHelper;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->encoder = $encoder;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
        $this->setId('masqueradegrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('erp_account_name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setTemplate('Epicor_Common::widget/grid/extended.phtml');

    }

    protected function _prepareLayout()
    {

        $customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */
        $masquerade = $customerSession->getMasqueradeAccountId();
        if ($masquerade) {
            $helper = $this->salesRepHelper;
            /* @var $helper \Epicor\SalesRep\Helper\Data */
            $isSecure = $helper->isSecure();
            $redirectUrl = $this->getUrl('salesrep/account/index', array('_forced_secure' => $isSecure));
            $returnUrl = $this->encoder->encode($redirectUrl);
            $ajax_url = $this->getUrl('comm/masquerade/masquerade', array('_forced_secure' => $isSecure, 'return_url' => $returnUrl));
            //$ajax_url = Mage::getBaseUrl().'comm/masquerade/masquerade?return_url='.$returnUrl;
            $this->setChild('add_button', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(array(
                    'label' => __('End Masquerade'),
                    'onclick' => "location.href='$ajax_url';",
                    'class' => 'task'
            )));
        }
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Mage_Adminhtml_Block_Widget_Grid
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getAddButtonHtml();
        return $html;
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag

        if ($column->getId() == 'selected_erpaccounts') {
            $ids = $this->_getSelected();
            if (!empty($ids)) {
                if ($column->getFilter()->getValue()) {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array(
                        'in' => $ids
                    ));
                } else {
                    $this->getCollection()->addFieldToFilter('main_table.entity_id', array(
                        'nin' => $ids
                    ));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     *
     * @return \Epicor\SalesRep\Model\Location
     */
    public function getSalesRepAccount()
    {
        if (!$this->_salesrep) {
            $helper = $this->salesRepAccountManageHelper;
            /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

            $salesRep = $helper->getManagedSalesRepAccount();
            $this->_salesrep = $salesRep;
        }

        return $this->_salesrep;
    }

    public function getSalesRepAccountChildrenIds()
    {
        if (!$this->_salesrepChildrenIds) {

            $salesRep = $this->getSalesRepAccount();
            $this->_salesrepChildrenIds = $salesRep->getHierarchyChildAccountsIds();
        }

        return $this->_salesrepChildrenIds;
    }

    protected function _prepareCollection()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */
        $salesRep = $helper->getBaseSalesRepAccount();
        $erpAccounts = $salesRep->getMasqueradeAccountIds();
        $collection->addFieldToFilter('main_table.entity_id', array(
            'in' => $erpAccounts
        ));

        if ($this->isColumnEnabled('invoice_address')) {

            $columns = array(
                'invoice_name' => 'name',
                'invoice_address1' => 'address1',
                'invoice_address2' => 'address2',
                'invoice_address3' => 'address3',
                'invoice_city' => 'city',
                'invoice_county' => 'county',
                'invoice_country' => 'country',
                'invoice_postcode' => 'postcode',
                'invoice_address_full' => 'CONCAT_WS(\' \', invoice_address.name, invoice_address.address1, invoice_address.address2, invoice_address.address3, invoice_address.city, invoice_address.county, invoice_address.country, invoice_address.postcode)',
            );

            $table = $collection->getTable('ecc_erp_account_address');
            $collection->getSelect()->joinLeft(array('invoice_address' => $table), 'invoice_address.erp_code = main_table.default_invoice_address_code AND invoice_address.erp_customer_group_code = main_table.erp_code ', $columns, null, 'left');
        }

        if ($this->isColumnEnabled('default_shipping_address')) {

            $columns = array(
                'shipping_name' => 'name',
                'shipping_address1' => 'address1',
                'shipping_address2' => 'address2',
                'shipping_address3' => 'address3',
                'shipping_city' => 'city',
                'shipping_county' => 'county',
                'shipping_country' => 'country',
                'shipping_postcode' => 'postcode',
                'shipping_address_full' => 'CONCAT_WS(\' \', shipping_address.name, shipping_address.address1, shipping_address.address2, shipping_address.address3, shipping_address.city, shipping_address.county, shipping_address.country, shipping_address.postcode)',
            );

            $table = $collection->getTable('ecc_erp_account_address');
            $collection->getSelect()->joinLeft(array('shipping_address' => $table), 'shipping_address.erp_code = main_table.default_delivery_address_code AND shipping_address.erp_customer_group_code = main_table.erp_code ', $columns, null, 'left');
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        $this->addColumn('erp_account_name', array(
            'header' => __('Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'main_table.name',
            'width' => '250px'
        ));

        if ($this->isColumnEnabled('account_number')) {
            $this->addColumn('account_number', array(
                'header' => __('Account Number'),
                'align' => 'left',
                'index' => 'account_number',
                'condition' => 'LIKE',
                'filter_index' => 'main_table.account_number'
                )
            );
        }

        if ($this->isColumnEnabled('short_code')) {
            $this->addColumn('short_code', array(
                'header' => __('Short Code'),
                'align' => 'left',
                'index' => 'short_code',
                'type' => 'text',
                'condition' => 'LIKE',
                'filter_index' => 'main_table.short_code',
                'width' => '150px'
                )
            );
        }

        if ($this->isColumnEnabled('invoice_address')) {
            $this->addColumn('invoice_address', array(
                'header' => __('Invoice Address'),
                'align' => 'left',
                'index' => 'invoice_address_full',
                'type' => 'text',
                'condition' => 'LIKE',
                'filter_condition_callback' => array($this, '_filterInvoice'),
                'renderer' => 'Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\Invoice',
                )
            );
        }

        if ($this->isColumnEnabled('default_shipping_address')) {
            $this->addColumn('default_shipping_address', array(
                'header' => __('Default Shipping Address'),
                'align' => 'left',
                'index' => 'shipping_address_full',
                'type' => 'text',
                'condition' => 'LIKE',
                'filter_condition_callback' => array($this, '_filterShipping'),
                'renderer' => 'Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\Shipping',
                )
            );
        }

        $this->addColumn('select', array(
            'header' => __('Masquerade as'),
            'width' => '140',
            'index' => 'id',
            'renderer' => 'Epicor\SalesRep\Block\Manage\Select\Grid\Renderer\Select',
            'links' => 'true',
            'getter' => 'getId',
            'header-align' => 'center',
            'align' => 'center',
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            'actions' => array(
                array(
                    'caption' => __('Begin Masquerade'),
                    'url' => 'javascript:void(0)',
                    'url_id'=>'',
                    'onclick' => 'selectMasquerade(this); return false;'
                )
            )
        ));

        $this->addColumn('row_id', array(
            'header' => __('Position'),
            'name' => 'row_id',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'entity_id',
            'width' => 0,
            'editable' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display'
        ));
        return parent::_prepareColumns();
    }

    protected function _getSelected()
    // Used in grid to return selected customers values.
    {
        return array_keys($this->getSelected());
    }

    public function getSelected()
    {
        if (empty($this->_selected) && $this->getRequest()->getParam('ajax') !== 'true') {
            $salesRep = $this->getSalesRepAccount();
            $erpAccountIds = $salesRep->getErpAccountIds();
            foreach ($erpAccountIds as $erpAccountId) {
                $this->_selected[$erpAccountId] = array(
                    'entity_id' => $erpAccountId
                );
            }
        }
        return $this->_selected;
    }

    public function setSelected($selected)
    {
        if (!empty($selected)) {
            foreach ($selected as $id) {
                $this->_selected[$id] = array(
                    'id' => $id
                );
            }
        }
    }

    public function getGridUrl()
    {

        $helper = $this->salesRepHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Data */
        $isSecure = $helper->isSecure();

        $params = array(
            'id' => $this->getSalesRepAccount()->getId(),
            '_current' => true,
            '_forced_secure' => $isSecure
        );
        return $this->getUrl('*/*/masqueradegrid', $params);
    }

    public function getRowUrl($row)
    {
        return null;
    }

    protected function _toHtml()
    {

        $html = parent::_toHtml();

        $html .= '<script>
        var FORM_KEY = "'.$this->getFormKey().'";
</script>';
        return $html;
    }

    protected function _salesRepAccountFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $salesRepIds = $this->getSalesRepAccountChildrenIds();

        $this->getCollection()->join(array(
            'salesrep_erp' => 'ecc_salesrep_erp_account'
            ), 'main_table.entity_id = salesrep_erp.erp_account_id', '')->join(array(
            'salesrep' => 'ecc_salesrep_account'
            ), 'salesrep.id = salesrep_erp.sales_rep_account_id', '');
        $this->getCollection()->getSelect()->group('main_table.entity_id');

        if (strtolower($value) == strtolower(__('This account'))) {
            $salesRepAccount = $this->getSalesRepAccount();
            /* @var $salesRepAccount Epicor_SalesRep_Model_Account */
            $salesRepAccountId = $salesRepAccount->getId();
            $this->getCollection()->addFieldtoFilter('salesrep.id', $salesRepAccountId);
        } elseif (strtolower($value) == strtolower(__('Child account'))) {
            $childrenSalesRepAccountsIds = $this->getSalesRepAccountChildrenIds();
            $this->getCollection()->addFieldtoFilter('salesrep.id', array(
                'in' => $childrenSalesRepAccountsIds
            ));
        } elseif (strtolower($value) == strtolower(__('Multiple accounts'))) {
            $childrenSalesRepAccountsIds = $this->getSalesRepAccountChildrenIds();
            $this->getCollection()->addFieldtoFilter('salesrep.id', array(
                'in' => $childrenSalesRepAccountsIds
            ));
            $this->getCollection()->getSelect()->having('COUNT(*) > 1');
        } else {
            $this->getCollection()->addFieldtoFilter('salesrep.name', array(
                'like' => "%$value%"
            ));
        }

        return $this;
    }

    public function isColumnEnabled($column)
    {
        return $this->scopeConfig->getValue('epicor_salesrep/masquerade_search/' . $column, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Filter Invoice condition
     *
     * @param \Epicor\Comm\Model\ResourceModel\Location\Product\Collection $collection
     * @param type $column
     *
     * @return void
     */
    public function _filterInvoice($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $collection->getSelect()->where(
            'CONCAT_WS(\' \', invoice_address.name, invoice_address.address1, invoice_address.address2, invoice_address.address3, invoice_address.city, invoice_address.county, invoice_address.country, invoice_address.postcode) LIKE ?', '%' . $value . '%'
        );
    }

    /**
     * Filter Invoice condition
     *
     * @param \Epicor\Comm\Model\ResourceModel\Location\Product\Collection $collection
     * @param type $column
     *
     * @return void
     */
    public function _filterShipping($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $collection->getSelect()->where(
            'CONCAT_WS(\' \', shipping_address.name, shipping_address.address1, shipping_address.address2, shipping_address.address3, shipping_address.city, shipping_address.county, shipping_address.country, shipping_address.postcode) LIKE ?', '%' . $value . '%'
        );
    }

}
